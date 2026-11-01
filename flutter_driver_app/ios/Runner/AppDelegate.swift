import Flutter
import UIKit
import GoogleMaps
import AVFoundation
import UserNotifications

@main
@objc class AppDelegate: FlutterAppDelegate {
  private var backgroundTaskId: UIBackgroundTaskIdentifier = .invalid

  override func application(
    _ application: UIApplication,
    didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]?
  ) -> Bool {
    GMSServices.provideAPIKey("AIzaSyACN52o17kFjtg_K45rKU_ETTJ6WaXvkC0")

    // Configure AVAudioSession so incoming order ringtones play loudly even on Lock Screen or in Background
    configureAudioSession()

    if #available(iOS 10.0, *) {
      UNUserNotificationCenter.current().delegate = self as? UNUserNotificationCenterDelegate
    }

    application.registerForRemoteNotifications()

    GeneratedPluginRegistrant.register(with: self)
    return super.application(application, didFinishLaunchingWithOptions: launchOptions)
  }

  private func configureAudioSession() {
    do {
      let session = AVAudioSession.sharedInstance()
      try session.setCategory(
        .playback,
        mode: .default,
        options: [.mixWithOthers, .duckOthers]
      )
      try session.setActive(true)
    } catch {
      print("[AppDelegate] AVAudioSession configuration error: \(error)")
    }
  }

  override func applicationWillResignActive(_ application: UIApplication) {
    configureAudioSession()
    super.applicationWillResignActive(application)
  }

  override func applicationDidEnterBackground(_ application: UIApplication) {
    configureAudioSession()
    if backgroundTaskId != .invalid {
      application.endBackgroundTask(backgroundTaskId)
      backgroundTaskId = .invalid
    }
    backgroundTaskId = application.beginBackgroundTask(withName: "RideMyCarsOrderMonitor") { [weak self] in
      guard let self = self else { return }
      if self.backgroundTaskId != .invalid {
        application.endBackgroundTask(self.backgroundTaskId)
        self.backgroundTaskId = .invalid
      }
    }
    super.applicationDidEnterBackground(application)
  }

  override func applicationDidBecomeActive(_ application: UIApplication) {
    if backgroundTaskId != .invalid {
      application.endBackgroundTask(backgroundTaskId)
      backgroundTaskId = .invalid
    }
    configureAudioSession()
    super.applicationDidBecomeActive(application)
  }
}
