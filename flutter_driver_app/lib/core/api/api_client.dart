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
          // Handle 401 unauthenticated by automatically re-logging in with saved credentials
          if (error.response?.statusCode == 401) {
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
