// ignore: unused_import
import 'package:intl/intl.dart' as intl;

import 'app_localizations.dart';

// ignore_for_file: type=lint

/// The translations for English (`en`).
class AppLocalizationsEn extends AppLocalizations {
  AppLocalizationsEn([String locale = 'en']) : super(locale);

  @override
  String get appTitle => 'Krishi Sathi';

  @override
  String get appTagline => 'Your farming companion';

  @override
  String get welcome => 'Welcome';

  @override
  String get chooseLanguage => 'Choose your language';

  @override
  String get whoAreYou => 'Who are you?';

  @override
  String get whoAreYouHint => 'We will show the most useful things first.';

  @override
  String get userTypeFarmer => 'Farmer';

  @override
  String get userTypeFarmerDesc => 'I grow crops in the field';

  @override
  String get userTypeGardener => 'Home / rooftop gardener';

  @override
  String get userTypeGardenerDesc => 'I grow vegetables or fruit at home';

  @override
  String get userTypeLearner => 'Student / enthusiast';

  @override
  String get userTypeLearnerDesc => 'I want to learn about farming';

  @override
  String get userTypeExtension => 'Extension worker';

  @override
  String get userTypeExtensionDesc => 'I advise farmers';

  @override
  String get chooseDistrict => 'Choose your district';

  @override
  String get chooseDistrictHint => 'For local weather and nearby market prices';

  @override
  String get searchDistrict => 'Search district';

  @override
  String get next => 'Next';

  @override
  String get back => 'Back';

  @override
  String get start => 'Start';

  @override
  String get today => 'Today';

  @override
  String get featureCropDoctor => 'Crop problems';

  @override
  String get featureCropDoctorDesc => 'Pests, diseases & solutions';

  @override
  String get featurePrices => 'Market prices';

  @override
  String get featurePricesDesc => 'Today\'s prices near you';

  @override
  String get featureWeather => 'Weather';

  @override
  String get featureWeatherDesc => 'Forecast & farm advice';

  @override
  String get featureAskExpert => 'Ask an expert';

  @override
  String get featureAskExpertDesc => 'Send a photo, get advice';

  @override
  String get featureMyCrops => 'My crops';

  @override
  String get featureMyCropsDesc => 'Task calendar for your field';

  @override
  String get featureCalculator => 'Fertilizer calculator';

  @override
  String get featureCalculatorDesc => 'How much, and the fair price';

  @override
  String get featureSowNow => 'What to plant now';

  @override
  String get featureSowNowDesc => 'Crops for this month';

  @override
  String get featureArticles => 'Farming tips';

  @override
  String get featureArticlesDesc => 'Guides for farm & rooftop';

  @override
  String callHelpline(String number) {
    return 'Free advice: call $number';
  }

  @override
  String get callHelplineDesc => 'Agriculture Call Centre';

  @override
  String get thisWeekTasks => 'This week\'s tasks';

  @override
  String get seeAll => 'See all';

  @override
  String offlineNotice(String time) {
    return 'No internet – showing saved information ($time)';
  }

  @override
  String get retry => 'Try again';

  @override
  String get errorNetwork => 'No internet connection.';

  @override
  String get errorServer => 'Server problem. Please try again later.';

  @override
  String get errorGeneric => 'Something went wrong.';

  @override
  String get errorTooMany => 'Too many attempts. Please wait a little.';

  @override
  String get errorSessionExpired => 'Please log in again.';

  @override
  String get justNow => 'just now';

  @override
  String minutesAgo(String n) {
    return '$n min ago';
  }

  @override
  String hoursAgo(String n) {
    return '$n hours ago';
  }

  @override
  String daysAgo(String n) {
    return '$n days ago';
  }

  @override
  String get searchCrops => 'Search crop';

  @override
  String get filterAll => 'All';

  @override
  String get filterGarden => 'Rooftop';

  @override
  String get categoryCereal => 'Cereals';

  @override
  String get categoryVegetable => 'Vegetables';

  @override
  String get categoryFruit => 'Fruits';

  @override
  String get categoryPulse => 'Pulses';

  @override
  String get categoryOilseed => 'Oilseeds';

  @override
  String get categoryFibre => 'Fibre';

  @override
  String get categorySpice => 'Spices';

  @override
  String get tabProblems => 'Problems';

  @override
  String get tabGuide => 'How to grow';

  @override
  String get tabCalendar => 'Calendar';

  @override
  String get tabFertilizer => 'Fertilizer';

  @override
  String get season => 'Season';

  @override
  String get seasonRabi => 'Rabi (winter)';

  @override
  String get seasonKharif1 => 'Kharif-1 (summer)';

  @override
  String get seasonKharif2 => 'Kharif-2 (monsoon)';

  @override
  String get seasonYearRound => 'Year round';

  @override
  String get sowingTime => 'Sowing / planting';

  @override
  String get harvestTime => 'Harvest';

  @override
  String get duration => 'Duration';

  @override
  String daysCount(String n) {
    return '$n days';
  }

  @override
  String get gardenFriendly => 'Good for rooftop';

  @override
  String get whereIsProblem => 'Where do you see the problem?';

  @override
  String get partAll => 'All';

  @override
  String get partLeaf => 'Leaf';

  @override
  String get partStem => 'Stem';

  @override
  String get partFruit => 'Fruit';

  @override
  String get partGrain => 'Grain/ear';

  @override
  String get partRoot => 'Root/tuber';

  @override
  String get partWhole => 'Whole plant';

  @override
  String get typePest => 'Insect pest';

  @override
  String get typeDisease => 'Disease';

  @override
  String get typeNutrient => 'Nutrient deficiency';

  @override
  String get typeWeed => 'Weed';

  @override
  String get typePhysiological => 'Other';

  @override
  String get severityHigh => 'Serious';

  @override
  String get symptoms => 'How to recognise it';

  @override
  String get prevention => 'Prevention & control – do this first';

  @override
  String get organicControl => 'Natural / organic control';

  @override
  String get chemicalControl => 'Chemical control – last resort';

  @override
  String get chemicalWarning =>
      'Use chemicals only when really needed. Wear a mask and gloves, follow the label dose and do not harvest before the waiting period ends.';

  @override
  String get notSureAskExpert => 'Not sure? Ask an expert';

  @override
  String get listen => 'Listen';

  @override
  String get stopListening => 'Stop';

  @override
  String get ttsUnavailable =>
      'Voice is not available on this phone. Install Google Text-to-Speech with Bangla.';

  @override
  String get noProblemsForPart => 'No problems listed for this part.';

  @override
  String stageDay(String n) {
    return 'Day $n';
  }

  @override
  String stageDaysBefore(String n) {
    return '$n days before';
  }

  @override
  String get calendarExplain => 'Day 0 = the day you sow or transplant.';

  @override
  String get addToMyCrops => 'Add to My crops';

  @override
  String get noCalendar => 'No calendar for this crop yet.';

  @override
  String get perBighaShort => 'per bigha';

  @override
  String get calculateForMyLand => 'Calculate for my land';

  @override
  String get noFertilizerData =>
      'No fertilizer recommendation for this crop yet.';

  @override
  String get photoTapToLoad => 'Tap to load photo';

  @override
  String get market => 'Market';

  @override
  String nearestMarket(String market) {
    return 'Nearest: $market';
  }

  @override
  String get wholesale => 'Wholesale';

  @override
  String get retail => 'Retail';

  @override
  String pricesOfDate(String date) {
    return 'Prices on $date';
  }

  @override
  String get trendUp => 'Rising';

  @override
  String get trendDown => 'Falling';

  @override
  String get trendStable => 'Stable';

  @override
  String get unitKg => 'kg';

  @override
  String get unitMaund => 'maund';

  @override
  String get unitPiece => 'piece';

  @override
  String get unitHali => 'hali (4)';

  @override
  String get unitDozen => 'dozen';

  @override
  String get unitLitre => 'litre';

  @override
  String perUnit(String unit) {
    return 'per $unit';
  }

  @override
  String get last30Days => 'Last 30 days';

  @override
  String get highest => 'Highest';

  @override
  String get lowest => 'Lowest';

  @override
  String get average => 'Average';

  @override
  String get demoDataWarning =>
      'Sample data for testing – not real market prices.';

  @override
  String get noPrices => 'No prices available yet.';

  @override
  String source(String source) {
    return 'Source: $source';
  }

  @override
  String get priceTip =>
      'Compare markets before selling. If prices fall at harvest time, storing part of the crop and selling later may pay more.';

  @override
  String get priceHistory => 'Price trend';

  @override
  String weatherIn(String district) {
    return 'Weather in $district';
  }

  @override
  String get farmAdvice => 'Farm advice';

  @override
  String get next7Days => 'Next 7 days';

  @override
  String get humidity => 'Humidity';

  @override
  String get rain => 'Rain';

  @override
  String get wind => 'Wind';

  @override
  String get millimetre => 'mm';

  @override
  String get staleWeather => 'Could not refresh – showing the last forecast.';

  @override
  String forecastSource(String source) {
    return 'Forecast: $source';
  }

  @override
  String get chooseDistrictFirst =>
      'Choose your district in Settings to see the weather.';

  @override
  String get wClear => 'Clear sky';

  @override
  String get wPartlyCloudy => 'Partly cloudy';

  @override
  String get wCloudy => 'Cloudy';

  @override
  String get wFog => 'Fog';

  @override
  String get wDrizzle => 'Drizzle';

  @override
  String get wRain => 'Rain';

  @override
  String get wShowers => 'Rain showers';

  @override
  String get wThunder => 'Thunderstorm';

  @override
  String get tomorrow => 'Tomorrow';

  @override
  String get myQuestions => 'My questions';

  @override
  String get communityAnswers => 'Answers for all';

  @override
  String get askQuestion => 'Ask a question';

  @override
  String get questionHint =>
      'Describe the problem: which crop, what you see, since when…';

  @override
  String get cropOptional => 'Crop (optional)';

  @override
  String get noCropSelected => 'General question';

  @override
  String get addPhoto => 'Add a photo – it helps a lot';

  @override
  String get camera => 'Camera';

  @override
  String get gallery => 'Gallery';

  @override
  String get removePhoto => 'Remove photo';

  @override
  String get send => 'Send';

  @override
  String get questionSent => 'Question sent. An expert will answer soon.';

  @override
  String get questionQueued =>
      'No internet. Your question is saved and will be sent automatically.';

  @override
  String pendingUploads(String n) {
    return '$n question(s) waiting to be sent';
  }

  @override
  String get sendNow => 'Send now';

  @override
  String get statusPending => 'Waiting for answer';

  @override
  String get statusAnswered => 'Answered';

  @override
  String get statusClosed => 'Closed';

  @override
  String get expertAnswer => 'Expert\'s answer';

  @override
  String get noQuestionsYet => 'You have not asked anything yet.';

  @override
  String get noCommunityAnswers => 'No public answers yet.';

  @override
  String urgentCall(String number) {
    return 'Urgent? Call $number – it is free';
  }

  @override
  String get questionTooShort => 'Please write a little more.';

  @override
  String get yourQuestion => 'Your question';

  @override
  String get login => 'Log in';

  @override
  String get register => 'Create account';

  @override
  String get loginNeeded => 'Log in to ask experts. It is free.';

  @override
  String get yourName => 'Your name';

  @override
  String get mobileNumber => 'Mobile number';

  @override
  String get pin => 'PIN (4–6 digits)';

  @override
  String get confirmPin => 'Type the PIN again';

  @override
  String get pinMismatch => 'The PINs do not match.';

  @override
  String get invalidPhone => 'Enter a valid number, e.g. 01712345678';

  @override
  String get invalidPin => 'PIN must be 4 to 6 digits.';

  @override
  String get requiredField => 'Required';

  @override
  String get haveAccount => 'Already have an account? Log in';

  @override
  String get noAccount => 'New here? Create an account';

  @override
  String get logout => 'Log out';

  @override
  String loggedInAs(String name) {
    return 'Logged in as $name';
  }

  @override
  String get pinHint => 'Remember your PIN and never share it.';

  @override
  String get myCropsEmpty =>
      'Add a crop to get a day-by-day task list for your field.';

  @override
  String get addCrop => 'Add crop';

  @override
  String get chooseCrop => 'Choose a crop';

  @override
  String get startDate => 'Sowing / transplanting date';

  @override
  String get plotName => 'Field name (optional)';

  @override
  String get plotNameHint => 'e.g. North field, Roof';

  @override
  String get save => 'Save';

  @override
  String get delete => 'Delete';

  @override
  String get deleteConfirm => 'Delete this crop and its task list?';

  @override
  String get cancel => 'Cancel';

  @override
  String cropDay(String n) {
    return 'Day $n';
  }

  @override
  String nextTask(String task) {
    return 'Next: $task';
  }

  @override
  String get allTasksDone => 'All tasks done';

  @override
  String get taskOverdue => 'Overdue';

  @override
  String get taskToday => 'Today';

  @override
  String taskInDays(String n) {
    return 'in $n days';
  }

  @override
  String taskDaysAgo(String n) {
    return '$n days ago';
  }

  @override
  String get markDone => 'Mark as done';

  @override
  String get noTasksThisWeek => 'No tasks this week.';

  @override
  String get savedOnPhone => 'Saved on this phone – works without internet.';

  @override
  String get landSize => 'Land size';

  @override
  String get unitDecimal => 'Decimal';

  @override
  String get unitKatha => 'Katha';

  @override
  String get unitBigha => 'Bigha';

  @override
  String get unitAcre => 'Acre';

  @override
  String get unitHectare => 'Hectare';

  @override
  String bighaNote(String n) {
    return '1 bigha = $n decimal here. Change it in Settings if your area differs.';
  }

  @override
  String get fertilizerNeeded => 'Fertilizer needed';

  @override
  String estimatedCost(String amount) {
    return 'Cost at government price: ৳$amount';
  }

  @override
  String get officialPrices => 'Government fertilizer prices';

  @override
  String get officialPriceNote =>
      'If a dealer charges more, ask for a receipt and inform the Upazila Agriculture Office.';

  @override
  String get calcDisclaimer =>
      'General recommendation for medium-fertility soil. A soil test gives the most accurate dose.';

  @override
  String kg(String n) {
    return '$n kg';
  }

  @override
  String gram(String n) {
    return '$n g';
  }

  @override
  String perKg(String n) {
    return '৳$n/kg';
  }

  @override
  String get whenToApply => 'When to apply';

  @override
  String get enterLandSize => 'Enter the size of your land';

  @override
  String sowInMonth(String month) {
    return 'Sow / plant in $month';
  }

  @override
  String harvestInMonth(String month) {
    return 'Harvest in $month';
  }

  @override
  String get rooftopOnly => 'Only rooftop-friendly';

  @override
  String get nothingThisMonth => 'Nothing listed for this month.';

  @override
  String get settings => 'Settings';

  @override
  String get language => 'Language';

  @override
  String get district => 'District';

  @override
  String get iAmA => 'I am a';

  @override
  String get textSize => 'Text size';

  @override
  String get textNormal => 'Normal';

  @override
  String get textLarge => 'Large';

  @override
  String get textExtraLarge => 'Extra large';

  @override
  String get dataSaver => 'Data saver';

  @override
  String get dataSaverDesc => 'Load photos only when you tap them';

  @override
  String get bighaSize => '1 bigha = how many decimal?';

  @override
  String get updateNow => 'Update information now';

  @override
  String contentUpdated(String time) {
    return 'Knowledge base updated $time';
  }

  @override
  String get contentBuiltIn => 'Using information built into the app';

  @override
  String get upToDate => 'Information is up to date.';

  @override
  String get serverAddress => 'Server address (advanced)';

  @override
  String get serverAddressHint =>
      'Enter your server\'s API address, e.g. https://api.example.com/api/v1. The Play Store app needs https; plain http (e.g. a laptop on the same Wi-Fi: http://192.168.0.10:8000/api/v1) only works in test builds.';

  @override
  String get resetDefault => 'Reset to default';

  @override
  String get serverConnected => '✓ Connected to the server.';

  @override
  String get errorWrongServer =>
      'No Krishi Sathi server found at this address. Check Settings → Server address, e.g. http://192.168.0.10:8000/api/v1';

  @override
  String get about => 'About';

  @override
  String get aboutText =>
      'Krishi Sathi gives farmers and gardeners in Bangladesh simple advice on crops, pests, fertilizer, weather and market prices. The advice is general – for serious problems contact your Upazila Agriculture Office or call 16123.';

  @override
  String version(String v) {
    return 'Version $v';
  }

  @override
  String get account => 'Account';

  @override
  String get privacyPolicy => 'Privacy policy';

  @override
  String get deleteAccount => 'Delete account';

  @override
  String get deleteAccountWarning =>
      'Your account, all your questions, photos and their answers will be deleted permanently. This cannot be undone. \"My crops\" saved on this phone stay.';

  @override
  String get deleteAccountPin => 'Enter your PIN to confirm';

  @override
  String get deleteAccountConfirm => 'Delete permanently';

  @override
  String get accountDeleted => 'Your account has been deleted.';

  @override
  String get updateAppTitle => 'Please update the app';

  @override
  String get updateAppText =>
      'This version is too old for some features (prices, questions). Install the new version from Google Play.';

  @override
  String get updateApp => 'Update';
}
