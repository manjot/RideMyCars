import 'dart:io';
import 'package:dio/dio.dart';
import 'package:dio/io.dart';
import '../constants/api_constants.dart';
import '../storage/token_storage.dart';

class ApiClient {
  static final ApiClient _instance = ApiClient._internal();
  factory ApiClient() => _instance;

  late final Dio dio;

  static const String _defaultUserAgent =
      'Mozilla/5.0 (Linux; Android 14; Mobile) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Mobile Safari/537.36 RideMyCarsDriver/1.0';

  ApiClient._internal() {
    dio = Dio(
      BaseOptions(
        baseUrl: ApiConstants.baseUrl,
        connectTimeout: const Duration(seconds: 35),
        receiveTimeout: const Duration(seconds: 35),
        sendTimeout: const Duration(seconds: 35),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'User-Agent': _defaultUserAgent,
        },
        followRedirects: true,
        maxRedirects: 5,
      ),
    );

    // Configure low-level HttpClient to avoid stale socket reuse and handle SSL renegotiation
    try {
      if (dio.httpClientAdapter is IOHttpClientAdapter) {
        (dio.httpClientAdapter as IOHttpClientAdapter).createHttpClient = () {
          final client = HttpClient();
          client.badCertificateCallback = (X509Certificate cert, String host, int port) => true;
          client.idleTimeout = const Duration(seconds: 10);
          client.connectionTimeout = const Duration(seconds: 35);
          return client;
        };
      }
    } catch (_) {}

    dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final token = await TokenStorage.getToken();
          if (token != null && token.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          if (!options.headers.containsKey('User-Agent') || options.headers['User-Agent'] == null) {
            options.headers['User-Agent'] = _defaultUserAgent;
          }
          return handler.next(options);
        },
        onError: (DioException error, handler) async {
          final statusCode = error.response?.statusCode;

          // 1. Transparently retry canonical www URL on any 301/302/307/308 redirect
          if (statusCode == 301 || statusCode == 302 || statusCode == 307 || statusCode == 308) {
            final location = error.response?.headers.value('location');
            String targetUri = location ?? error.requestOptions.uri.toString();
            if (!targetUri.contains('www.ridemycars.com')) {
              targetUri = targetUri.replaceFirst('https://ridemycars.com', 'https://www.ridemycars.com')
                                   .replaceFirst('http://ridemycars.com', 'https://www.ridemycars.com');
            }
            try {
              final token = await TokenStorage.getToken();
              final retryOptions = Options(
                method: error.requestOptions.method,
                headers: {
                  ...error.requestOptions.headers,
                  if (token != null && token.isNotEmpty) 'Authorization': 'Bearer $token',
                },
                responseType: error.requestOptions.responseType,
                contentType: error.requestOptions.contentType,
              );
              final retryRes = await dio.request(
                targetUri,
                data: error.requestOptions.data,
                options: retryOptions,
                queryParameters: error.requestOptions.queryParameters,
              );
              return handler.resolve(retryRes);
            } catch (_) {
              // Pass original error through if retry fails
            }
          }

          // 2. Transparently retry once on transient network/socket/timeout errors (e.g. stale keepalive socket closed by server after user entered OTP)
          final isNetworkError = error.type == DioExceptionType.connectionError ||
              error.type == DioExceptionType.connectionTimeout ||
              error.type == DioExceptionType.sendTimeout ||
              error.type == DioExceptionType.receiveTimeout;
          if (isNetworkError) {
            final retries = (error.requestOptions.extra['retry_count'] as int?) ?? 0;
            if (retries < 2) {
              error.requestOptions.extra['retry_count'] = retries + 1;
              try {
                await Future.delayed(const Duration(milliseconds: 600));
                final retryRes = await dio.fetch(error.requestOptions);
                return handler.resolve(retryRes);
              } catch (_) {
                // If retry also failed, continue to next handler
              }
            }
          }

          // 3. Handle 401 unauthenticated by automatically re-logging in with saved credentials
          // Never attempt re-login if the request itself was /logout or /login
          if (statusCode == 401) {
            final path = error.requestOptions.path;
            if (path.contains('/logout') || path.contains('/login')) {
              return handler.next(error);
            }

            final email = await TokenStorage.getUserEmail();
            final password = await TokenStorage.getSavedPassword();

            if (email != null && password != null && email.isNotEmpty && password.isNotEmpty) {
              try {
                final authDio = Dio(
                  BaseOptions(
                    baseUrl: ApiConstants.baseUrl,
                    connectTimeout: const Duration(seconds: 35),
                    receiveTimeout: const Duration(seconds: 35),
                    headers: {
                      'Accept': 'application/json',
                      'Content-Type': 'application/json',
                      'User-Agent': _defaultUserAgent,
                    },
                  ),
                );

                final res = await authDio.post(ApiConstants.login, data: {
                  'email': email,
                  'password': password,
                });

                if (res.statusCode == 200 && res.data['token'] != null) {
                  final newToken = res.data['token'].toString();
                  await TokenStorage.saveToken(newToken);

                  // Retry the original request with the fresh new token
                  final opts = error.requestOptions;
                  opts.headers['Authorization'] = 'Bearer $newToken';

                  final retryResponse = await authDio.request(
                    opts.path,
                    options: Options(
                      method: opts.method,
                      headers: opts.headers,
                    ),
                    data: opts.data,
                    queryParameters: opts.queryParameters,
                  );

                  return handler.resolve(retryResponse);
                }
              } catch (_) {
                // Ignore and pass error through
              }
            }
          }
          return handler.next(error);
        },
      ),
    );
  }
}
