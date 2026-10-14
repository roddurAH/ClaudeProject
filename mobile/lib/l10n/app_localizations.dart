import 'dart:async';

import 'package:flutter/foundation.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:intl/intl.dart' as intl;

import 'app_localizations_bn.dart';
import 'app_localizations_en.dart';

// ignore_for_file: type=lint

/// Callers can lookup localized strings with an instance of AppLocalizations
/// returned by `AppLocalizations.of(context)`.
///
/// Applications need to include `AppLocalizations.delegate()` in their app's
/// `localizationDelegates` list, and the locales they support in the app's
/// `supportedLocales` list. For example:
///
/// ```dart
/// import 'l10n/app_localizations.dart';
///
/// return MaterialApp(
///   localizationsDelegates: AppLocalizations.localizationsDelegates,
///   supportedLocales: AppLocalizations.supportedLocales,
///   home: MyApplicationHome(),
/// );
/// ```
///
/// ## Update pubspec.yaml
///
/// Please make sure to update your pubspec.yaml to include the following
/// packages:
///
/// ```yaml
/// dependencies:
///   # Internationalization support.
///   flutter_localizations:
///     sdk: flutter
///   intl: any # Use the pinned version from flutter_localizations
///
///   # Rest of dependencies
/// ```
///
/// ## iOS Applications
///
/// iOS applications define key application metadata, including supported
/// locales, in an Info.plist file that is built into the application bundle.
/// To configure the locales supported by your app, you’ll need to edit this
/// file.
///
/// First, open your project’s ios/Runner.xcworkspace Xcode workspace file.
/// Then, in the Project Navigator, open the Info.plist file under the Runner
/// project’s Runner folder.
///
/// Next, select the Information Property List item, select Add Item from the
/// Editor menu, then select Localizations from the pop-up menu.
///
/// Select and expand the newly-created Localizations item then, for each
/// locale your application supports, add a new item and select the locale
/// you wish to add from the pop-up menu in the Value field. This list should
/// be consistent with the languages listed in the AppLocalizations.supportedLocales
/// property.
abstract class AppLocalizations {
  AppLocalizations(String locale)
    : localeName = intl.Intl.canonicalizedLocale(locale.toString());

  final String localeName;

  static AppLocalizations of(BuildContext context) {
    return Localizations.of<AppLocalizations>(context, AppLocalizations)!;
  }

  static const LocalizationsDelegate<AppLocalizations> delegate =
      _AppLocalizationsDelegate();

  /// A list of this localizations delegate along with the default localizations
  /// delegates.
  ///
  /// Returns a list of localizations delegates containing this delegate along with
  /// GlobalMaterialLocalizations.delegate, GlobalCupertinoLocalizations.delegate,
  /// and GlobalWidgetsLocalizations.delegate.
  ///
  /// Additional delegates can be added by appending to this list in
  /// MaterialApp. This list does not have to be used at all if a custom list
  /// of delegates is preferred or required.
  static const List<LocalizationsDelegate<dynamic>> localizationsDelegates =
      <LocalizationsDelegate<dynamic>>[
        delegate,
        GlobalMaterialLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
      ];

  /// A list of this localizations delegate's supported locales.
  static const List<Locale> supportedLocales = <Locale>[
    Locale('bn'),
    Locale('en'),
  ];

  /// No description provided for @appTitle.
  ///
  /// In en, this message translates to:
  /// **'Krishi Sathi'**
  String get appTitle;

  /// No description provided for @appTagline.
  ///
  /// In en, this message translates to:
  /// **'Your farming companion'**
  String get appTagline;

  /// No description provided for @welcome.
  ///
  /// In en, this message translates to:
  /// **'Welcome'**
  String get welcome;

  /// No description provided for @chooseLanguage.
  ///
  /// In en, this message translates to:
  /// **'Choose your language'**
  String get chooseLanguage;

  /// No description provided for @whoAreYou.
  ///
  /// In en, this message translates to:
  /// **'Who are you?'**
  String get whoAreYou;

  /// No description provided for @whoAreYouHint.
  ///
  /// In en, this message translates to:
  /// **'We will show the most useful things first.'**
  String get whoAreYouHint;

  /// No description provided for @userTypeFarmer.
  ///
  /// In en, this message translates to:
  /// **'Farmer'**
  String get userTypeFarmer;

  /// No description provided for @userTypeFarmerDesc.
  ///
  /// In en, this message translates to:
  /// **'I grow crops in the field'**
  String get userTypeFarmerDesc;

  /// No description provided for @userTypeGardener.
  ///
  /// In en, this message translates to:
  /// **'Home / rooftop gardener'**
  String get userTypeGardener;

  /// No description provided for @userTypeGardenerDesc.
  ///
  /// In en, this message translates to:
  /// **'I grow vegetables or fruit at home'**
  String get userTypeGardenerDesc;

  /// No description provided for @userTypeLearner.
  ///
  /// In en, this message translates to:
  /// **'Student / enthusiast'**
  String get userTypeLearner;

  /// No description provided for @userTypeLearnerDesc.
  ///
  /// In en, this message translates to:
  /// **'I want to learn about farming'**
  String get userTypeLearnerDesc;

  /// No description provided for @userTypeExtension.
  ///
  /// In en, this message translates to:
  /// **'Extension worker'**
  String get userTypeExtension;

  /// No description provided for @userTypeExtensionDesc.
  ///
  /// In en, this message translates to:
  /// **'I advise farmers'**
  String get userTypeExtensionDesc;

  /// No description provided for @chooseDistrict.
  ///
  /// In en, this message translates to:
  /// **'Choose your district'**
  String get chooseDistrict;

  /// No description provided for @chooseDistrictHint.
  ///
  /// In en, this message translates to:
  /// **'For local weather and nearby market prices'**
  String get chooseDistrictHint;

  /// No description provided for @searchDistrict.
  ///
  /// In en, this message translates to:
  /// **'Search district'**
  String get searchDistrict;

  /// No description provided for @next.
  ///
  /// In en, this message translates to:
  /// **'Next'**
  String get next;

  /// No description provided for @back.
  ///
  /// In en, this message translates to:
  /// **'Back'**
  String get back;

  /// No description provided for @start.
  ///
  /// In en, this message translates to:
  /// **'Start'**
  String get start;

  /// No description provided for @today.
  ///
  /// In en, this message translates to:
  /// **'Today'**
  String get today;

  /// No description provided for @featureCropDoctor.
  ///
  /// In en, this message translates to:
  /// **'Crop problems'**
  String get featureCropDoctor;

  /// No description provided for @featureCropDoctorDesc.
  ///
  /// In en, this message translates to:
  /// **'Pests, diseases & solutions'**
  String get featureCropDoctorDesc;

  /// No description provided for @featurePrices.
  ///
  /// In en, this message translates to:
  /// **'Market prices'**
  String get featurePrices;

  /// No description provided for @featurePricesDesc.
  ///
  /// In en, this message translates to:
  /// **'Today\'s prices near you'**
  String get featurePricesDesc;

  /// No description provided for @featureWeather.
  ///
  /// In en, this message translates to:
  /// **'Weather'**
  String get featureWeather;

  /// No description provided for @featureWeatherDesc.
  ///
  /// In en, this message translates to:
  /// **'Forecast & farm advice'**
  String get featureWeatherDesc;

  /// No description provided for @featureAskExpert.
  ///
  /// In en, this message translates to:
  /// **'Ask an expert'**
  String get featureAskExpert;

  /// No description provided for @featureAskExpertDesc.
  ///
  /// In en, this message translates to:
  /// **'Send a photo, get advice'**
  String get featureAskExpertDesc;

  /// No description provided for @featureMyCrops.
  ///
  /// In en, this message translates to:
  /// **'My crops'**
  String get featureMyCrops;

  /// No description provided for @featureMyCropsDesc.
  ///
  /// In en, this message translates to:
  /// **'Task calendar for your field'**
  String get featureMyCropsDesc;

  /// No description provided for @featureCalculator.
  ///
  /// In en, this message translates to:
  /// **'Fertilizer calculator'**
  String get featureCalculator;

  /// No description provided for @featureCalculatorDesc.
  ///
  /// In en, this message translates to:
  /// **'How much, and the fair price'**
  String get featureCalculatorDesc;

  /// No description provided for @featureSowNow.
  ///
  /// In en, this message translates to:
  /// **'What to plant now'**
  String get featureSowNow;

  /// No description provided for @featureSowNowDesc.
  ///
  /// In en, this message translates to:
  /// **'Crops for this month'**
  String get featureSowNowDesc;

  /// No description provided for @featureArticles.
  ///
  /// In en, this message translates to:
  /// **'Farming tips'**
  String get featureArticles;

  /// No description provided for @featureArticlesDesc.
  ///
  /// In en, this message translates to:
  /// **'Guides for farm & rooftop'**
  String get featureArticlesDesc;

  /// No description provided for @callHelpline.
  ///
  /// In en, this message translates to:
  /// **'Free advice: call {number}'**
  String callHelpline(String number);

  /// No description provided for @callHelplineDesc.
  ///
  /// In en, this message translates to:
  /// **'Agriculture Call Centre'**
  String get callHelplineDesc;

  /// No description provided for @thisWeekTasks.
  ///
  /// In en, this message translates to:
  /// **'This week\'s tasks'**
  String get thisWeekTasks;

  /// No description provided for @seeAll.
  ///
  /// In en, this message translates to:
  /// **'See all'**
  String get seeAll;

  /// No description provided for @offlineNotice.
  ///
  /// In en, this message translates to:
  /// **'No internet – showing saved information ({time})'**
  String offlineNotice(String time);

  /// No description provided for @retry.
  ///
  /// In en, this message translates to:
  /// **'Try again'**
  String get retry;

  /// No description provided for @errorNetwork.
  ///
  /// In en, this message translates to:
  /// **'No internet connection.'**
  String get errorNetwork;

  /// No description provided for @errorServer.
  ///
  /// In en, this message translates to:
  /// **'Server problem. Please try again later.'**
  String get errorServer;

  /// No description provided for @errorGeneric.
  ///
  /// In en, this message translates to:
  /// **'Something went wrong.'**
  String get errorGeneric;

  /// No description provided for @errorTooMany.
  ///
  /// In en, this message translates to:
  /// **'Too many attempts. Please wait a little.'**
  String get errorTooMany;

  /// No description provided for @errorSessionExpired.
  ///
  /// In en, this message translates to:
  /// **'Please log in again.'**
  String get errorSessionExpired;

  /// No description provided for @justNow.
  ///
  /// In en, this message translates to:
  /// **'just now'**
  String get justNow;

  /// No description provided for @minutesAgo.
  ///
  /// In en, this message translates to:
  /// **'{n} min ago'**
  String minutesAgo(String n);

  /// No description provided for @hoursAgo.
  ///
  /// In en, this message translates to:
  /// **'{n} hours ago'**
  String hoursAgo(String n);

  /// No description provided for @daysAgo.
  ///
  /// In en, this message translates to:
  /// **'{n} days ago'**
  String daysAgo(String n);

  /// No description provided for @searchCrops.
  ///
  /// In en, this message translates to:
  /// **'Search crop'**
  String get searchCrops;

  /// No description provided for @filterAll.
  ///
  /// In en, this message translates to:
  /// **'All'**
  String get filterAll;

  /// No description provided for @filterGarden.
  ///
  /// In en, this message translates to:
  /// **'Rooftop'**
  String get filterGarden;

  /// No description provided for @categoryCereal.
  ///
  /// In en, this message translates to:
  /// **'Cereals'**
  String get categoryCereal;

  /// No description provided for @categoryVegetable.
  ///
  /// In en, this message translates to:
  /// **'Vegetables'**
  String get categoryVegetable;

  /// No description provided for @categoryFruit.
  ///
  /// In en, this message translates to:
  /// **'Fruits'**
  String get categoryFruit;

  /// No description provided for @categoryPulse.
  ///
  /// In en, this message translates to:
  /// **'Pulses'**
  String get categoryPulse;

  /// No description provided for @categoryOilseed.
  ///
  /// In en, this message translates to:
  /// **'Oilseeds'**
  String get categoryOilseed;

  /// No description provided for @categoryFibre.
  ///
  /// In en, this message translates to:
  /// **'Fibre'**
  String get categoryFibre;

  /// No description provided for @categorySpice.
  ///
  /// In en, this message translates to:
  /// **'Spices'**
  String get categorySpice;

  /// No description provided for @tabProblems.
  ///
  /// In en, this message translates to:
  /// **'Problems'**
  String get tabProblems;

  /// No description provided for @tabGuide.
  ///
  /// In en, this message translates to:
  /// **'How to grow'**
  String get tabGuide;

  /// No description provided for @tabCalendar.
  ///
  /// In en, this message translates to:
  /// **'Calendar'**
  String get tabCalendar;

  /// No description provided for @tabFertilizer.
  ///
  /// In en, this message translates to:
  /// **'Fertilizer'**
  String get tabFertilizer;

  /// No description provided for @season.
  ///
  /// In en, this message translates to:
  /// **'Season'**
  String get season;

  /// No description provided for @seasonRabi.
  ///
  /// In en, this message translates to:
  /// **'Rabi (winter)'**
  String get seasonRabi;

  /// No description provided for @seasonKharif1.
  ///
  /// In en, this message translates to:
  /// **'Kharif-1 (summer)'**
  String get seasonKharif1;

  /// No description provided for @seasonKharif2.
  ///
  /// In en, this message translates to:
  /// **'Kharif-2 (monsoon)'**
  String get seasonKharif2;

  /// No description provided for @seasonYearRound.
  ///
  /// In en, this message translates to:
  /// **'Year round'**
  String get seasonYearRound;

  /// No description provided for @sowingTime.
  ///
  /// In en, this message translates to:
  /// **'Sowing / planting'**
  String get sowingTime;

  /// No description provided for @harvestTime.
  ///
  /// In en, this message translates to:
  /// **'Harvest'**
  String get harvestTime;

  /// No description provided for @duration.
  ///
  /// In en, this message translates to:
  /// **'Duration'**
  String get duration;

  /// No description provided for @daysCount.
  ///
  /// In en, this message translates to:
  /// **'{n} days'**
  String daysCount(String n);

  /// No description provided for @gardenFriendly.
  ///
  /// In en, this message translates to:
  /// **'Good for rooftop'**
  String get gardenFriendly;

  /// No description provided for @whereIsProblem.
  ///
  /// In en, this message translates to:
  /// **'Where do you see the problem?'**
  String get whereIsProblem;

  /// No description provided for @partAll.
  ///
  /// In en, this message translates to:
  /// **'All'**
  String get partAll;

  /// No description provided for @partLeaf.
  ///
  /// In en, this message translates to:
  /// **'Leaf'**
  String get partLeaf;

  /// No description provided for @partStem.
  ///
  /// In en, this message translates to:
  /// **'Stem'**
  String get partStem;

  /// No description provided for @partFruit.
  ///
  /// In en, this message translates to:
  /// **'Fruit'**
  String get partFruit;

  /// No description provided for @partGrain.
  ///
  /// In en, this message translates to:
  /// **'Grain/ear'**
  String get partGrain;

  /// No description provided for @partRoot.
  ///
  /// In en, this message translates to:
  /// **'Root/tuber'**
  String get partRoot;

  /// No description provided for @partWhole.
  ///
  /// In en, this message translates to:
  /// **'Whole plant'**
  String get partWhole;

  /// No description provided for @typePest.
  ///
  /// In en, this message translates to:
  /// **'Insect pest'**
  String get typePest;

  /// No description provided for @typeDisease.
  ///
  /// In en, this message translates to:
  /// **'Disease'**
  String get typeDisease;

  /// No description provided for @typeNutrient.
  ///
  /// In en, this message translates to:
  /// **'Nutrient deficiency'**
  String get typeNutrient;

  /// No description provided for @typeWeed.
  ///
  /// In en, this message translates to:
  /// **'Weed'**
  String get typeWeed;

  /// No description provided for @typePhysiological.
  ///
  /// In en, this message translates to:
  /// **'Other'**
  String get typePhysiological;

  /// No description provided for @severityHigh.
  ///
  /// In en, this message translates to:
  /// **'Serious'**
  String get severityHigh;

  /// No description provided for @symptoms.
  ///
  /// In en, this message translates to:
  /// **'How to recognise it'**
  String get symptoms;

  /// No description provided for @prevention.
  ///
  /// In en, this message translates to:
  /// **'Prevention & control – do this first'**
  String get prevention;

  /// No description provided for @organicControl.
  ///
  /// In en, this message translates to:
  /// **'Natural / organic control'**
  String get organicControl;

  /// No description provided for @chemicalControl.
  ///
  /// In en, this message translates to:
  /// **'Chemical control – last resort'**
  String get chemicalControl;

  /// No description provided for @chemicalWarning.
  ///
  /// In en, this message translates to:
  /// **'Use chemicals only when really needed. Wear a mask and gloves, follow the label dose and do not harvest before the waiting period ends.'**
  String get chemicalWarning;

  /// No description provided for @notSureAskExpert.
  ///
  /// In en, this message translates to:
  /// **'Not sure? Ask an expert'**
  String get notSureAskExpert;

  /// No description provided for @listen.
  ///
  /// In en, this message translates to:
  /// **'Listen'**
  String get listen;

  /// No description provided for @stopListening.
  ///
  /// In en, this message translates to:
  /// **'Stop'**
  String get stopListening;

  /// No description provided for @ttsUnavailable.
  ///
  /// In en, this message translates to:
  /// **'Voice is not available on this phone. Install Google Text-to-Speech with Bangla.'**
  String get ttsUnavailable;

  /// No description provided for @noProblemsForPart.
  ///
  /// In en, this message translates to:
  /// **'No problems listed for this part.'**
  String get noProblemsForPart;

  /// No description provided for @stageDay.
  ///
  /// In en, this message translates to:
  /// **'Day {n}'**
  String stageDay(String n);

  /// No description provided for @stageDaysBefore.
  ///
  /// In en, this message translates to:
  /// **'{n} days before'**
  String stageDaysBefore(String n);

  /// No description provided for @calendarExplain.
  ///
  /// In en, this message translates to:
  /// **'Day 0 = the day you sow or transplant.'**
  String get calendarExplain;

  /// No description provided for @addToMyCrops.
  ///
  /// In en, this message translates to:
  /// **'Add to My crops'**
  String get addToMyCrops;

  /// No description provided for @noCalendar.
  ///
  /// In en, this message translates to:
  /// **'No calendar for this crop yet.'**
  String get noCalendar;

  /// No description provided for @perBighaShort.
  ///
  /// In en, this message translates to:
  /// **'per bigha'**
  String get perBighaShort;

  /// No description provided for @calculateForMyLand.
  ///
  /// In en, this message translates to:
  /// **'Calculate for my land'**
  String get calculateForMyLand;

  /// No description provided for @noFertilizerData.
  ///
  /// In en, this message translates to:
  /// **'No fertilizer recommendation for this crop yet.'**
  String get noFertilizerData;

  /// No description provided for @photoTapToLoad.
  ///
  /// In en, this message translates to:
  /// **'Tap to load photo'**
  String get photoTapToLoad;

  /// No description provided for @market.
  ///
  /// In en, this message translates to:
  /// **'Market'**
  String get market;

  /// No description provided for @nearestMarket.
  ///
  /// In en, this message translates to:
  /// **'Nearest: {market}'**
  String nearestMarket(String market);

  /// No description provided for @wholesale.
  ///
  /// In en, this message translates to:
  /// **'Wholesale'**
  String get wholesale;

  /// No description provided for @retail.
  ///
  /// In en, this message translates to:
  /// **'Retail'**
  String get retail;

  /// No description provided for @pricesOfDate.
  ///
  /// In en, this message translates to:
  /// **'Prices on {date}'**
  String pricesOfDate(String date);

  /// No description provided for @trendUp.
  ///
  /// In en, this message translates to:
  /// **'Rising'**
  String get trendUp;

  /// No description provided for @trendDown.
  ///
  /// In en, this message translates to:
  /// **'Falling'**
  String get trendDown;

  /// No description provided for @trendStable.
  ///
  /// In en, this message translates to:
  /// **'Stable'**
  String get trendStable;

  /// No description provided for @unitKg.
  ///
  /// In en, this message translates to:
  /// **'kg'**
  String get unitKg;

  /// No description provided for @unitMaund.
  ///
  /// In en, this message translates to:
  /// **'maund'**
  String get unitMaund;

  /// No description provided for @unitPiece.
  ///
  /// In en, this message translates to:
  /// **'piece'**
  String get unitPiece;

  /// No description provided for @unitHali.
  ///
  /// In en, this message translates to:
  /// **'hali (4)'**
  String get unitHali;

  /// No description provided for @unitDozen.
  ///
  /// In en, this message translates to:
  /// **'dozen'**
  String get unitDozen;

  /// No description provided for @unitLitre.
  ///
  /// In en, this message translates to:
  /// **'litre'**
  String get unitLitre;

  /// No description provided for @perUnit.
  ///
  /// In en, this message translates to:
  /// **'per {unit}'**
  String perUnit(String unit);

  /// No description provided for @last30Days.
  ///
  /// In en, this message translates to:
  /// **'Last 30 days'**
  String get last30Days;

  /// No description provided for @highest.
  ///
  /// In en, this message translates to:
  /// **'Highest'**
  String get highest;

  /// No description provided for @lowest.
  ///
  /// In en, this message translates to:
  /// **'Lowest'**
  String get lowest;

  /// No description provided for @average.
  ///
  /// In en, this message translates to:
  /// **'Average'**
  String get average;

  /// No description provided for @demoDataWarning.
  ///
  /// In en, this message translates to:
  /// **'Sample data for testing – not real market prices.'**
  String get demoDataWarning;

  /// No description provided for @noPrices.
  ///
  /// In en, this message translates to:
  /// **'No prices available yet.'**
  String get noPrices;

  /// No description provided for @source.
  ///
  /// In en, this message translates to:
  /// **'Source: {source}'**
  String source(String source);

  /// No description provided for @priceTip.
  ///
  /// In en, this message translates to:
  /// **'Compare markets before selling. If prices fall at harvest time, storing part of the crop and selling later may pay more.'**
  String get priceTip;

  /// No description provided for @priceHistory.
  ///
  /// In en, this message translates to:
  /// **'Price trend'**
  String get priceHistory;

  /// No description provided for @weatherIn.
  ///
  /// In en, this message translates to:
  /// **'Weather in {district}'**
  String weatherIn(String district);

  /// No description provided for @farmAdvice.
  ///
  /// In en, this message translates to:
  /// **'Farm advice'**
  String get farmAdvice;

  /// No description provided for @next7Days.
  ///
  /// In en, this message translates to:
  /// **'Next 7 days'**
  String get next7Days;

  /// No description provided for @humidity.
  ///
  /// In en, this message translates to:
  /// **'Humidity'**
  String get humidity;

  /// No description provided for @rain.
  ///
  /// In en, this message translates to:
  /// **'Rain'**
  String get rain;

  /// No description provided for @wind.
  ///
  /// In en, this message translates to:
  /// **'Wind'**
  String get wind;

  /// No description provided for @millimetre.
  ///
  /// In en, this message translates to:
  /// **'mm'**
  String get millimetre;

  /// No description provided for @staleWeather.
  ///
  /// In en, this message translates to:
  /// **'Could not refresh – showing the last forecast.'**
  String get staleWeather;

  /// No description provided for @forecastSource.
  ///
  /// In en, this message translates to:
  /// **'Forecast: {source}'**
  String forecastSource(String source);

  /// No description provided for @chooseDistrictFirst.
  ///
  /// In en, this message translates to:
  /// **'Choose your district in Settings to see the weather.'**
  String get chooseDistrictFirst;

  /// No description provided for @wClear.
  ///
  /// In en, this message translates to:
  /// **'Clear sky'**
  String get wClear;

  /// No description provided for @wPartlyCloudy.
  ///
  /// In en, this message translates to:
  /// **'Partly cloudy'**
  String get wPartlyCloudy;

  /// No description provided for @wCloudy.
  ///
  /// In en, this message translates to:
  /// **'Cloudy'**
  String get wCloudy;

  /// No description provided for @wFog.
  ///
  /// In en, this message translates to:
  /// **'Fog'**
  String get wFog;

  /// No description provided for @wDrizzle.
  ///
  /// In en, this message translates to:
  /// **'Drizzle'**
  String get wDrizzle;

  /// No description provided for @wRain.
  ///
  /// In en, this message translates to:
  /// **'Rain'**
  String get wRain;

  /// No description provided for @wShowers.
  ///
  /// In en, this message translates to:
  /// **'Rain showers'**
  String get wShowers;

  /// No description provided for @wThunder.
  ///
  /// In en, this message translates to:
  /// **'Thunderstorm'**
  String get wThunder;

  /// No description provided for @tomorrow.
  ///
  /// In en, this message translates to:
  /// **'Tomorrow'**
  String get tomorrow;

  /// No description provided for @myQuestions.
  ///
  /// In en, this message translates to:
  /// **'My questions'**
  String get myQuestions;

  /// No description provided for @communityAnswers.
  ///
  /// In en, this message translates to:
  /// **'Answers for all'**
  String get communityAnswers;

  /// No description provided for @askQuestion.
  ///
  /// In en, this message translates to:
  /// **'Ask a question'**
  String get askQuestion;

  /// No description provided for @questionHint.
  ///
  /// In en, this message translates to:
  /// **'Describe the problem: which crop, what you see, since when…'**
  String get questionHint;

  /// No description provided for @cropOptional.
  ///
  /// In en, this message translates to:
  /// **'Crop (optional)'**
  String get cropOptional;

  /// No description provided for @noCropSelected.
  ///
  /// In en, this message translates to:
  /// **'General question'**
  String get noCropSelected;

  /// No description provided for @addPhoto.
  ///
  /// In en, this message translates to:
  /// **'Add a photo – it helps a lot'**
  String get addPhoto;

  /// No description provided for @camera.
  ///
  /// In en, this message translates to:
  /// **'Camera'**
  String get camera;

  /// No description provided for @gallery.
  ///
  /// In en, this message translates to:
  /// **'Gallery'**
  String get gallery;

  /// No description provided for @removePhoto.
  ///
  /// In en, this message translates to:
  /// **'Remove photo'**
  String get removePhoto;

  /// No description provided for @send.
  ///
  /// In en, this message translates to:
  /// **'Send'**
  String get send;

  /// No description provided for @questionSent.
  ///
  /// In en, this message translates to:
  /// **'Question sent. An expert will answer soon.'**
  String get questionSent;

  /// No description provided for @questionQueued.
  ///
  /// In en, this message translates to:
  /// **'No internet. Your question is saved and will be sent automatically.'**
  String get questionQueued;

  /// No description provided for @pendingUploads.
  ///
  /// In en, this message translates to:
  /// **'{n} question(s) waiting to be sent'**
  String pendingUploads(String n);

  /// No description provided for @sendNow.
  ///
  /// In en, this message translates to:
  /// **'Send now'**
  String get sendNow;

  /// No description provided for @statusPending.
  ///
  /// In en, this message translates to:
  /// **'Waiting for answer'**
  String get statusPending;

  /// No description provided for @statusAnswered.
  ///
  /// In en, this message translates to:
  /// **'Answered'**
  String get statusAnswered;

  /// No description provided for @statusClosed.
  ///
  /// In en, this message translates to:
  /// **'Closed'**
  String get statusClosed;

  /// No description provided for @expertAnswer.
  ///
  /// In en, this message translates to:
  /// **'Expert\'s answer'**
  String get expertAnswer;

  /// No description provided for @noQuestionsYet.
  ///
  /// In en, this message translates to:
  /// **'You have not asked anything yet.'**
  String get noQuestionsYet;

  /// No description provided for @noCommunityAnswers.
  ///
  /// In en, this message translates to:
  /// **'No public answers yet.'**
  String get noCommunityAnswers;

  /// No description provided for @urgentCall.
  ///
  /// In en, this message translates to:
  /// **'Urgent? Call {number} – it is free'**
  String urgentCall(String number);

  /// No description provided for @questionTooShort.
  ///
  /// In en, this message translates to:
  /// **'Please write a little more.'**
  String get questionTooShort;

  /// No description provided for @yourQuestion.
  ///
  /// In en, this message translates to:
  /// **'Your question'**
  String get yourQuestion;

  /// No description provided for @login.
  ///
  /// In en, this message translates to:
  /// **'Log in'**
  String get login;

  /// No description provided for @register.
  ///
  /// In en, this message translates to:
  /// **'Create account'**
  String get register;

  /// No description provided for @loginNeeded.
  ///
  /// In en, this message translates to:
  /// **'Log in to ask experts. It is free.'**
  String get loginNeeded;

  /// No description provided for @yourName.
  ///
  /// In en, this message translates to:
  /// **'Your name'**
  String get yourName;

  /// No description provided for @mobileNumber.
  ///
  /// In en, this message translates to:
  /// **'Mobile number'**
  String get mobileNumber;

  /// No description provided for @pin.
  ///
  /// In en, this message translates to:
  /// **'PIN (4–6 digits)'**
  String get pin;

  /// No description provided for @confirmPin.
  ///
  /// In en, this message translates to:
  /// **'Type the PIN again'**
  String get confirmPin;

  /// No description provided for @pinMismatch.
  ///
  /// In en, this message translates to:
  /// **'The PINs do not match.'**
  String get pinMismatch;

  /// No description provided for @invalidPhone.
  ///
  /// In en, this message translates to:
  /// **'Enter a valid number, e.g. 01712345678'**
  String get invalidPhone;

  /// No description provided for @invalidPin.
  ///
  /// In en, this message translates to:
  /// **'PIN must be 4 to 6 digits.'**
  String get invalidPin;

  /// No description provided for @requiredField.
  ///
  /// In en, this message translates to:
  /// **'Required'**
  String get requiredField;

  /// No description provided for @haveAccount.
  ///
  /// In en, this message translates to:
  /// **'Already have an account? Log in'**
  String get haveAccount;

  /// No description provided for @noAccount.
  ///
  /// In en, this message translates to:
  /// **'New here? Create an account'**
  String get noAccount;

  /// No description provided for @logout.
  ///
  /// In en, this message translates to:
  /// **'Log out'**
  String get logout;

  /// No description provided for @loggedInAs.
  ///
  /// In en, this message translates to:
  /// **'Logged in as {name}'**
  String loggedInAs(String name);

  /// No description provided for @pinHint.
  ///
  /// In en, this message translates to:
  /// **'Remember your PIN and never share it.'**
  String get pinHint;

  /// No description provided for @myCropsEmpty.
  ///
  /// In en, this message translates to:
  /// **'Add a crop to get a day-by-day task list for your field.'**
  String get myCropsEmpty;

  /// No description provided for @addCrop.
  ///
  /// In en, this message translates to:
  /// **'Add crop'**
  String get addCrop;

  /// No description provided for @chooseCrop.
  ///
  /// In en, this message translates to:
  /// **'Choose a crop'**
  String get chooseCrop;

  /// No description provided for @startDate.
  ///
  /// In en, this message translates to:
  /// **'Sowing / transplanting date'**
  String get startDate;

  /// No description provided for @plotName.
  ///
  /// In en, this message translates to:
  /// **'Field name (optional)'**
  String get plotName;

  /// No description provided for @plotNameHint.
  ///
  /// In en, this message translates to:
  /// **'e.g. North field, Roof'**
  String get plotNameHint;

  /// No description provided for @save.
  ///
  /// In en, this message translates to:
  /// **'Save'**
  String get save;

  /// No description provided for @delete.
  ///
  /// In en, this message translates to:
  /// **'Delete'**
  String get delete;

  /// No description provided for @deleteConfirm.
  ///
  /// In en, this message translates to:
  /// **'Delete this crop and its task list?'**
  String get deleteConfirm;

  /// No description provided for @cancel.
  ///
  /// In en, this message translates to:
  /// **'Cancel'**
  String get cancel;

  /// No description provided for @cropDay.
  ///
  /// In en, this message translates to:
  /// **'Day {n}'**
  String cropDay(String n);

  /// No description provided for @nextTask.
  ///
  /// In en, this message translates to:
  /// **'Next: {task}'**
  String nextTask(String task);

  /// No description provided for @allTasksDone.
  ///
  /// In en, this message translates to:
  /// **'All tasks done'**
  String get allTasksDone;

  /// No description provided for @taskOverdue.
  ///
  /// In en, this message translates to:
  /// **'Overdue'**
  String get taskOverdue;

  /// No description provided for @taskToday.
  ///
  /// In en, this message translates to:
  /// **'Today'**
  String get taskToday;

  /// No description provided for @taskInDays.
  ///
  /// In en, this message translates to:
  /// **'in {n} days'**
  String taskInDays(String n);

  /// No description provided for @taskDaysAgo.
  ///
  /// In en, this message translates to:
  /// **'{n} days ago'**
  String taskDaysAgo(String n);

  /// No description provided for @markDone.
  ///
  /// In en, this message translates to:
  /// **'Mark as done'**
  String get markDone;

  /// No description provided for @noTasksThisWeek.
  ///
  /// In en, this message translates to:
  /// **'No tasks this week.'**
  String get noTasksThisWeek;

  /// No description provided for @savedOnPhone.
  ///
  /// In en, this message translates to:
  /// **'Saved on this phone – works without internet.'**
  String get savedOnPhone;

  /// No description provided for @landSize.
  ///
  /// In en, this message translates to:
  /// **'Land size'**
  String get landSize;

  /// No description provided for @unitDecimal.
  ///
  /// In en, this message translates to:
  /// **'Decimal'**
  String get unitDecimal;

  /// No description provided for @unitKatha.
  ///
  /// In en, this message translates to:
  /// **'Katha'**
  String get unitKatha;

  /// No description provided for @unitBigha.
  ///
  /// In en, this message translates to:
  /// **'Bigha'**
  String get unitBigha;

  /// No description provided for @unitAcre.
  ///
  /// In en, this message translates to:
  /// **'Acre'**
  String get unitAcre;

  /// No description provided for @unitHectare.
  ///
  /// In en, this message translates to:
  /// **'Hectare'**
  String get unitHectare;

  /// No description provided for @bighaNote.
  ///
  /// In en, this message translates to:
  /// **'1 bigha = {n} decimal here. Change it in Settings if your area differs.'**
  String bighaNote(String n);

  /// No description provided for @fertilizerNeeded.
  ///
  /// In en, this message translates to:
  /// **'Fertilizer needed'**
  String get fertilizerNeeded;

  /// No description provided for @estimatedCost.
  ///
  /// In en, this message translates to:
  /// **'Cost at government price: ৳{amount}'**
  String estimatedCost(String amount);

  /// No description provided for @officialPrices.
  ///
  /// In en, this message translates to:
  /// **'Government fertilizer prices'**
  String get officialPrices;

  /// No description provided for @officialPriceNote.
  ///
  /// In en, this message translates to:
  /// **'If a dealer charges more, ask for a receipt and inform the Upazila Agriculture Office.'**
  String get officialPriceNote;

  /// No description provided for @calcDisclaimer.
  ///
  /// In en, this message translates to:
  /// **'General recommendation for medium-fertility soil. A soil test gives the most accurate dose.'**
  String get calcDisclaimer;

  /// No description provided for @kg.
  ///
  /// In en, this message translates to:
  /// **'{n} kg'**
  String kg(String n);

  /// No description provided for @gram.
  ///
  /// In en, this message translates to:
  /// **'{n} g'**
  String gram(String n);

  /// No description provided for @perKg.
  ///
  /// In en, this message translates to:
  /// **'৳{n}/kg'**
  String perKg(String n);

  /// No description provided for @whenToApply.
  ///
  /// In en, this message translates to:
  /// **'When to apply'**
  String get whenToApply;

  /// No description provided for @enterLandSize.
  ///
  /// In en, this message translates to:
  /// **'Enter the size of your land'**
  String get enterLandSize;

  /// No description provided for @sowInMonth.
  ///
  /// In en, this message translates to:
  /// **'Sow / plant in {month}'**
  String sowInMonth(String month);

  /// No description provided for @harvestInMonth.
  ///
  /// In en, this message translates to:
  /// **'Harvest in {month}'**
  String harvestInMonth(String month);

  /// No description provided for @rooftopOnly.
  ///
  /// In en, this message translates to:
  /// **'Only rooftop-friendly'**
  String get rooftopOnly;

  /// No description provided for @nothingThisMonth.
  ///
  /// In en, this message translates to:
  /// **'Nothing listed for this month.'**
  String get nothingThisMonth;

  /// No description provided for @settings.
  ///
  /// In en, this message translates to:
  /// **'Settings'**
  String get settings;

  /// No description provided for @language.
  ///
  /// In en, this message translates to:
  /// **'Language'**
  String get language;

  /// No description provided for @district.
  ///
  /// In en, this message translates to:
  /// **'District'**
  String get district;

  /// No description provided for @iAmA.
  ///
  /// In en, this message translates to:
  /// **'I am a'**
  String get iAmA;

  /// No description provided for @textSize.
  ///
  /// In en, this message translates to:
  /// **'Text size'**
  String get textSize;

  /// No description provided for @textNormal.
  ///
  /// In en, this message translates to:
  /// **'Normal'**
  String get textNormal;

  /// No description provided for @textLarge.
  ///
  /// In en, this message translates to:
  /// **'Large'**
  String get textLarge;

  /// No description provided for @textExtraLarge.
  ///
  /// In en, this message translates to:
  /// **'Extra large'**
  String get textExtraLarge;

  /// No description provided for @dataSaver.
  ///
  /// In en, this message translates to:
  /// **'Data saver'**
  String get dataSaver;

  /// No description provided for @dataSaverDesc.
  ///
  /// In en, this message translates to:
  /// **'Load photos only when you tap them'**
  String get dataSaverDesc;

  /// No description provided for @bighaSize.
  ///
  /// In en, this message translates to:
  /// **'1 bigha = how many decimal?'**
  String get bighaSize;

  /// No description provided for @updateNow.
  ///
  /// In en, this message translates to:
  /// **'Update information now'**
  String get updateNow;

  /// No description provided for @contentUpdated.
  ///
  /// In en, this message translates to:
  /// **'Knowledge base updated {time}'**
  String contentUpdated(String time);

  /// No description provided for @contentBuiltIn.
  ///
  /// In en, this message translates to:
  /// **'Using information built into the app'**
  String get contentBuiltIn;

  /// No description provided for @upToDate.
  ///
  /// In en, this message translates to:
  /// **'Information is up to date.'**
  String get upToDate;

  /// No description provided for @serverAddress.
  ///
  /// In en, this message translates to:
  /// **'Server address (advanced)'**
  String get serverAddress;

  /// No description provided for @serverAddressHint.
  ///
  /// In en, this message translates to:
  /// **'Enter your server\'s API address, e.g. https://api.example.com/api/v1. The Play Store app needs https; plain http (e.g. a laptop on the same Wi-Fi: http://192.168.0.10:8000/api/v1) only works in test builds.'**
  String get serverAddressHint;

  /// No description provided for @resetDefault.
  ///
  /// In en, this message translates to:
  /// **'Reset to default'**
  String get resetDefault;

  /// No description provided for @serverConnected.
  ///
  /// In en, this message translates to:
  /// **'✓ Connected to the server.'**
  String get serverConnected;

  /// No description provided for @errorWrongServer.
  ///
  /// In en, this message translates to:
  /// **'No Krishi Sathi server found at this address. Check Settings → Server address, e.g. http://192.168.0.10:8000/api/v1'**
  String get errorWrongServer;

  /// No description provided for @about.
  ///
  /// In en, this message translates to:
  /// **'About'**
  String get about;

  /// No description provided for @aboutText.
  ///
  /// In en, this message translates to:
  /// **'Krishi Sathi gives farmers and gardeners in Bangladesh simple advice on crops, pests, fertilizer, weather and market prices. The advice is general – for serious problems contact your Upazila Agriculture Office or call 16123.'**
  String get aboutText;

  /// No description provided for @version.
  ///
  /// In en, this message translates to:
  /// **'Version {v}'**
  String version(String v);

  /// No description provided for @account.
  ///
  /// In en, this message translates to:
  /// **'Account'**
  String get account;

  /// No description provided for @privacyPolicy.
  ///
  /// In en, this message translates to:
  /// **'Privacy policy'**
  String get privacyPolicy;

  /// No description provided for @deleteAccount.
  ///
  /// In en, this message translates to:
  /// **'Delete account'**
  String get deleteAccount;

  /// No description provided for @deleteAccountWarning.
  ///
  /// In en, this message translates to:
  /// **'Your account, all your questions, photos and their answers will be deleted permanently. This cannot be undone. \"My crops\" saved on this phone stay.'**
  String get deleteAccountWarning;

  /// No description provided for @deleteAccountPin.
  ///
  /// In en, this message translates to:
  /// **'Enter your PIN to confirm'**
  String get deleteAccountPin;

  /// No description provided for @deleteAccountConfirm.
  ///
  /// In en, this message translates to:
  /// **'Delete permanently'**
  String get deleteAccountConfirm;

  /// No description provided for @accountDeleted.
  ///
  /// In en, this message translates to:
  /// **'Your account has been deleted.'**
  String get accountDeleted;

  /// No description provided for @updateAppTitle.
  ///
  /// In en, this message translates to:
  /// **'Please update the app'**
  String get updateAppTitle;

  /// No description provided for @updateAppText.
  ///
  /// In en, this message translates to:
  /// **'This version is too old for some features (prices, questions). Install the new version from Google Play.'**
  String get updateAppText;

  /// No description provided for @updateApp.
  ///
  /// In en, this message translates to:
  /// **'Update'**
  String get updateApp;
}

class _AppLocalizationsDelegate
    extends LocalizationsDelegate<AppLocalizations> {
  const _AppLocalizationsDelegate();

  @override
  Future<AppLocalizations> load(Locale locale) {
    return SynchronousFuture<AppLocalizations>(lookupAppLocalizations(locale));
  }

  @override
  bool isSupported(Locale locale) =>
      <String>['bn', 'en'].contains(locale.languageCode);

  @override
  bool shouldReload(_AppLocalizationsDelegate old) => false;
}

AppLocalizations lookupAppLocalizations(Locale locale) {
  // Lookup logic when only language code is specified.
  switch (locale.languageCode) {
    case 'bn':
      return AppLocalizationsBn();
    case 'en':
      return AppLocalizationsEn();
  }

  throw FlutterError(
    'AppLocalizations.delegate failed to load unsupported locale "$locale". This is likely '
    'an issue with the localizations generation tool. Please file an issue '
    'on GitHub with a reproducible sample app and the gen-l10n configuration '
    'that was used.',
  );
}
