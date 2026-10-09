class ApiConstants {
  static const String baseUrl = 'https://www.ridemycars.com/api';
  static const String storageBaseUrl = 'https://www.ridemycars.com/storage';

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
  static const String activeRide = '/rides/active';
  static String rideStatus(int id) => '/rides/$id/status';
  static String rideVerifyPin(int id) => '/rides/$id/verify-pin';
  static String rideCancel(int id) => '/rides/$id/cancel';

  // Driver
  static const String driverLocation = '/driver/location';
  static const String driverToggleAvailability = '/driver/toggle-availability';
  static const String driverRequests = '/driver/requests';
  static const String driverRespond = '/driver/respond';
  static const String driverPendingVerifications = '/driver/pending-verifications';
  static const String driverVerifyBooking = '/driver/verify-booking';
  static const String driverActiveRides = '/driver/active-rides';
  static const String driverEarnings = '/driver/earnings';
  static const String driverProfile = '/driver/profile';
  static const String driverPhoto = '/driver/photo';
  static const String driverIncentives = '/driver/incentives';
  static const String driverIncentivesHistory = '/driver/incentives/history';

  // Notifications
  static const String notifications = '/notifications';
  static const String notificationsMarkRead = '/notifications/mark-read';

  // Wallet & Withdrawals
  static const String walletBalance = '/wallet/balance';
  static const String walletWithdraw = '/wallet/withdraw';
  static const String walletWithdrawals = '/wallet/withdrawals';
  static const String walletPayoutMethods = '/wallet/payout-methods';
  static const String walletTransactions = '/wallet/transactions';

  // Emergency SOS & Trusted Contacts
  static const String emergencyContacts = '/emergency-contacts';
  static const String sosTrigger = '/sos/trigger';
}


