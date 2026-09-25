import 'package:dio/dio.dart';
import '../constants/api_constants.dart';
import '../storage/token_storage.dart';

class ApiClient {
  static final ApiClient _instance = ApiClient._internal();
  factory ApiClient() => _instance;

  late final Dio dio;

  ApiClient._internal() {
    dio = Dio(
      BaseOptions(
        baseUrl: ApiConstants.baseUrl,
        connectTimeout: const Duration(seconds: 15),
        receiveTimeout: const Duration(seconds: 15),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        followRedirects: true,
        maxRedirects: 5,
      ),
    );

    dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final token = await TokenStorage.getToken();
          if (token != null && token.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          return handler.next(options);
        },
        onError: (DioException error, handler) async {
          // 1. Transparently retry canonical www URL on any 301/302/307/308 redirect
          final statusCode = error.response?.statusCode;
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

          // 2. Handle 401 unauthenticated by automatically re-logging in with saved credentials
          if (statusCode == 401) {
            final email = await TokenStorage.getUserEmail();
            final password = await TokenStorage.getSavedPassword();

            if (email != null && password != null && email.isNotEmpty && password.isNotEmpty) {
              try {
                final authDio = Dio(
                  BaseOptions(
                    baseUrl: ApiConstants.baseUrl,
                    headers: {
                      'Accept': 'application/json',
                      'Content-Type': 'application/json',
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
