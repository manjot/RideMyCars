class ApiConstants {
  static const String baseUrl = 'https://ridemycars.com/api';
  static const String storageBaseUrl = 'https://ridemycars.com/storage';
  static const String googleMapsApiKey = 'AIzaSyACN52o17kFjtg_K45rKU_ETTJ6WaXvkC0';

  // Auth
  static const String login = '/login';
  static const String register = '/register';
  static const String sendOtp = '/otp/send';
  static const String verifyOtp = '/otp/verify';
  static const String googleAuth = '/auth/social/google';
  static const String appleAuth = '/auth/social/apple';
  static const String googleOAuthUrl = 'https://ridemycars.com/auth/google';
  static const String appleOAuthUrl = 'https://ridemycars.com/auth/apple';
  static const String me = '/me';
  static const String logout = '/logout';

  // Rides
  static const String rides = '/rides';
  static const String activeRide = '/rides/active';
  static String rideStatus(int id) => '/rides/$id/status';
  static String rideCancel(int id) => '/rides/$id/cancel';

  // Driver
  static const String driverLocation = '/driver/location';
  static const String driverToggleAvailability = '/driver/toggle-availability';
  static const String driverRequests = '/driver/requests';
  static const String driverRespond = '/driver/respond';
  static const String driverActiveRides = '/driver/active-rides';
  static const String driverEarnings = '/driver/earnings';

  // Notifications
  static const String notifications = '/notifications';
  static const String notificationsMarkRead = '/notifications/mark-read';
}
