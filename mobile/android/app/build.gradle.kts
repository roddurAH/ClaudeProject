import java.io.FileInputStream
import java.util.Properties

plugins {
    id("com.android.application")
    // The Flutter Gradle Plugin must be applied after the Android and Kotlin Gradle plugins.
    id("dev.flutter.flutter-gradle-plugin")
}

// ── Release signing ───────────────────────────────────────────────────────
// Put your upload key details in android/key.properties (never commit it):
//   storeFile=/full/path/to/upload-keystore.jks
//   storePassword=…
//   keyAlias=upload
//   keyPassword=…
// See docs/ANDROID_RELEASE.md. Without that file, release builds are signed
// with the debug key: fine for installing on test phones, rejected by Play.
// Set KRISHI_UNSIGNED_RELEASE=true to produce an unsigned build to sign later.
val keystorePropertiesFile = rootProject.file("key.properties")
val keystoreProperties = Properties().apply {
    if (keystorePropertiesFile.exists()) load(FileInputStream(keystorePropertiesFile))
}
val unsignedRelease = System.getenv("KRISHI_UNSIGNED_RELEASE") == "true"

android {
    namespace = "com.krishisathi.app"
    compileSdk = flutter.compileSdkVersion
    ndkVersion = flutter.ndkVersion

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }

    defaultConfig {
        // Permanent Play Store identity – never change after the first upload.
        applicationId = "com.krishisathi.app"
        minSdk = flutter.minSdkVersion // Android 7.0+
        targetSdk = flutter.targetSdkVersion
        // From pubspec.yaml "version: 1.0.0+1" → versionName 1.0.0, versionCode 1.
        // Increase the number after "+" for every Play Store upload.
        versionCode = flutter.versionCode
        versionName = flutter.versionName
    }

    signingConfigs {
        if (keystorePropertiesFile.exists()) {
            create("release") {
                keyAlias = keystoreProperties["keyAlias"] as String
                keyPassword = keystoreProperties["keyPassword"] as String
                storeFile = file(keystoreProperties["storeFile"] as String)
                storePassword = keystoreProperties["storePassword"] as String
            }
        }
    }

    buildTypes {
        release {
            signingConfig = when {
                unsignedRelease -> null
                keystorePropertiesFile.exists() -> signingConfigs.getByName("release")
                else -> {
                    logger.warn("⚠ android/key.properties not found – release build is signed with the DEBUG key (not accepted by Google Play).")
                    signingConfigs.getByName("debug")
                }
            }
        }
    }
}

kotlin {
    compilerOptions {
        jvmTarget = org.jetbrains.kotlin.gradle.dsl.JvmTarget.JVM_17
    }
}

flutter {
    source = "../.."
}
