class ApiConstants {
  static const String baseUrl = 'https://www.ridemycars.com/api';
  static const String storageBaseUrl = 'https://www.ridemycars.com/storage';
  static const String googleMapsApiKey = 'AIzaSyACN52o17kFjtg_K45rKU_ETTJ6WaXvkC0';

  // Auth
  static const String login = '/login';
  static const String register = '/register';
  static const String sendOtp = '/otp/send';
  static const String verifyOtp = '/otp/verify';
  static const String googleAuth = '/auth/social/google';
  static const String appleAuth = '/auth/social/apple';
  static const String googleOAuthUrl = 'https://www.ridemycars.com/auth/google';
  static const String appleOAuthUrl = 'https://www.ridemycars.com/auth/apple';
  static const String me = '/me';
  static const String logout = '/logout';

  // Rides
  static const String rides = '/rides';
  static const String rideCategories = '/rides/categories';
  static const String rideCalculatePrice = '/rides/calculate-price';
  static const String activeRide = '/rides/active';
  static String rideStatus(int id) => '/rides/$id/status';
  static String rideCancel(int id) => '/rides/$id/cancel';
  static String rideBackupConfirm(int id) => '/rides/$id/backup/confirm';
  static String rideBackupDecline(int id) => '/rides/$id/backup/decline';
  static String rideBackupToggle(int id) => '/rides/$id/backup/toggle';

  // Rentals & Vehicles
  static const String rentalVehicles = '/rent/search';
  static const String vehicles = '/vehicles';

  // Driver
  static const String drivers = '/drivers';
  static String driverDetail(int id) => '/drivers/$id';
  static const String driverLocation = '/driver/location';
  static const String driverToggleAvailability = '/driver/toggle-availability';
  static const String driverRequests = '/driver/requests';
  static const String driverRespond = '/driver/respond';
  static const String driverActiveRides = '/driver/active-rides';
  static const String driverEarnings = '/driver/earnings';

  // Notifications
  static const String notifications = '/notifications';
  static const String notificationsMarkRead = '/notifications/mark-read';

  // Delivery
  static const String deliveryCalculate = '/delivery/calculate-price';
  static const String deliveryBook = '/delivery/book';
  static String deliveryStatus(int id) => '/delivery/$id/status';

  // Receipts
  static const String receipts = '/receipts';
  static String receiptDetail(int id) => '/receipts/$id';
  static String receiptDownload(int id) => '/receipts/$id/download';
  static String receiptResend(int id) => '/receipts/$id/resend';
  static String receiptViewWeb(String token) => 'https://www.ridemycars.com/receipts/$token';
  static String receiptDownloadWeb(String token) => 'https://www.ridemycars.com/receipts/$token/download';
}
