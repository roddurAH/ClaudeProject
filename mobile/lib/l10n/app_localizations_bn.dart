// ignore: unused_import
import 'package:intl/intl.dart' as intl;

import 'app_localizations.dart';

// ignore_for_file: type=lint

/// The translations for Bengali Bangla (`bn`).
class AppLocalizationsBn extends AppLocalizations {
  AppLocalizationsBn([String locale = 'bn']) : super(locale);

  @override
  String get appTitle => 'কৃষি সাথী';

  @override
  String get appTagline => 'আপনার কৃষি সহায়ক';

  @override
  String get welcome => 'স্বাগতম';

  @override
  String get chooseLanguage => 'ভাষা বেছে নিন';

  @override
  String get whoAreYou => 'আপনি কে?';

  @override
  String get whoAreYouHint => 'আপনার জন্য সবচেয়ে দরকারি তথ্য আগে দেখাব।';

  @override
  String get userTypeFarmer => 'কৃষক';

  @override
  String get userTypeFarmerDesc => 'আমি মাঠে ফসল চাষ করি';

  @override
  String get userTypeGardener => 'ছাদ বা বাড়ির বাগানি';

  @override
  String get userTypeGardenerDesc => 'আমি বাড়িতে বা ছাদে সবজি-ফল চাষ করি';

  @override
  String get userTypeLearner => 'শিক্ষার্থী / কৃষিপ্রেমী';

  @override
  String get userTypeLearnerDesc => 'আমি কৃষি সম্পর্কে জানতে চাই';

  @override
  String get userTypeExtension => 'কৃষি কর্মী';

  @override
  String get userTypeExtensionDesc => 'আমি কৃষকদের পরামর্শ দিই';

  @override
  String get chooseDistrict => 'আপনার জেলা বেছে নিন';

  @override
  String get chooseDistrictHint => 'এলাকার আবহাওয়া ও কাছের বাজারদরের জন্য';

  @override
  String get searchDistrict => 'জেলা খুঁজুন';

  @override
  String get next => 'পরবর্তী';

  @override
  String get back => 'পেছনে';

  @override
  String get start => 'শুরু করুন';

  @override
  String get today => 'আজ';

  @override
  String get featureCropDoctor => 'ফসলের সমস্যা';

  @override
  String get featureCropDoctorDesc => 'পোকা, রোগ ও সমাধান';

  @override
  String get featurePrices => 'বাজার দর';

  @override
  String get featurePricesDesc => 'আপনার কাছের বাজারের আজকের দর';

  @override
  String get featureWeather => 'আবহাওয়া';

  @override
  String get featureWeatherDesc => 'পূর্বাভাস ও কৃষি পরামর্শ';

  @override
  String get featureAskExpert => 'বিশেষজ্ঞকে প্রশ্ন';

  @override
  String get featureAskExpertDesc => 'ছবি পাঠান, পরামর্শ নিন';

  @override
  String get featureMyCrops => 'আমার ফসল';

  @override
  String get featureMyCropsDesc => 'আপনার জমির কাজের তালিকা';

  @override
  String get featureCalculator => 'সার হিসাব';

  @override
  String get featureCalculatorDesc => 'কতটুকু সার, ন্যায্য দাম কত';

  @override
  String get featureSowNow => 'এখন কী লাগাবেন';

  @override
  String get featureSowNowDesc => 'এই মাসের উপযোগী ফসল';

  @override
  String get featureArticles => 'কৃষি তথ্য';

  @override
  String get featureArticlesDesc => 'মাঠ ও ছাদ বাগানের নির্দেশিকা';

  @override
  String callHelpline(String number) {
    return 'বিনামূল্যে পরামর্শ: কল করুন $number';
  }

  @override
  String get callHelplineDesc => 'কৃষি কল সেন্টার';

  @override
  String get thisWeekTasks => 'এই সপ্তাহের কাজ';

  @override
  String get seeAll => 'সব দেখুন';

  @override
  String offlineNotice(String time) {
    return 'ইন্টারনেট নেই – সংরক্ষিত তথ্য দেখানো হচ্ছে ($time)';
  }

  @override
  String get retry => 'আবার চেষ্টা করুন';

  @override
  String get errorNetwork => 'ইন্টারনেট সংযোগ নেই।';

  @override
  String get errorServer => 'সার্ভারে সমস্যা। একটু পরে আবার চেষ্টা করুন।';

  @override
  String get errorGeneric => 'কিছু একটা সমস্যা হয়েছে।';

  @override
  String get errorTooMany =>
      'অনেকবার চেষ্টা করা হয়েছে। কিছুক্ষণ অপেক্ষা করুন।';

  @override
  String get errorSessionExpired => 'অনুগ্রহ করে আবার লগইন করুন।';

  @override
  String get justNow => 'এইমাত্র';

  @override
  String minutesAgo(String n) {
    return '$n মিনিট আগে';
  }

  @override
  String hoursAgo(String n) {
    return '$n ঘণ্টা আগে';
  }

  @override
  String daysAgo(String n) {
    return '$n দিন আগে';
  }

  @override
  String get searchCrops => 'ফসল খুঁজুন';

  @override
  String get filterAll => 'সব';

  @override
  String get filterGarden => 'ছাদ বাগান';

  @override
  String get categoryCereal => 'দানাশস্য';

  @override
  String get categoryVegetable => 'সবজি';

  @override
  String get categoryFruit => 'ফল';

  @override
  String get categoryPulse => 'ডাল';

  @override
  String get categoryOilseed => 'তেলবীজ';

  @override
  String get categoryFibre => 'আঁশ';

  @override
  String get categorySpice => 'মসলা';

  @override
  String get tabProblems => 'সমস্যা';

  @override
  String get tabGuide => 'চাষ পদ্ধতি';

  @override
  String get tabCalendar => 'ক্যালেন্ডার';

  @override
  String get tabFertilizer => 'সার';

  @override
  String get season => 'মৌসুম';

  @override
  String get seasonRabi => 'রবি (শীতকাল)';

  @override
  String get seasonKharif1 => 'খরিফ-১ (গ্রীষ্মকাল)';

  @override
  String get seasonKharif2 => 'খরিফ-২ (বর্ষাকাল)';

  @override
  String get seasonYearRound => 'সারা বছর';

  @override
  String get sowingTime => 'বপন / রোপণ';

  @override
  String get harvestTime => 'ফসল তোলা';

  @override
  String get duration => 'সময়কাল';

  @override
  String daysCount(String n) {
    return '$n দিন';
  }

  @override
  String get gardenFriendly => 'ছাদে চাষযোগ্য';

  @override
  String get whereIsProblem => 'সমস্যা কোথায় দেখছেন?';

  @override
  String get partAll => 'সব';

  @override
  String get partLeaf => 'পাতা';

  @override
  String get partStem => 'কাণ্ড/ডগা';

  @override
  String get partFruit => 'ফল';

  @override
  String get partGrain => 'শীষ/দানা';

  @override
  String get partRoot => 'শিকড়/কন্দ';

  @override
  String get partWhole => 'পুরো গাছ';

  @override
  String get typePest => 'পোকা';

  @override
  String get typeDisease => 'রোগ';

  @override
  String get typeNutrient => 'পুষ্টির অভাব';

  @override
  String get typeWeed => 'আগাছা';

  @override
  String get typePhysiological => 'অন্যান্য';

  @override
  String get severityHigh => 'মারাত্মক';

  @override
  String get symptoms => 'কীভাবে চিনবেন';

  @override
  String get prevention => 'প্রতিরোধ ও দমন – প্রথমে এগুলো করুন';

  @override
  String get organicControl => 'প্রাকৃতিক / জৈব দমন';

  @override
  String get chemicalControl => 'রাসায়নিক দমন – শেষ উপায়';

  @override
  String get chemicalWarning =>
      'খুব প্রয়োজন হলেই রাসায়নিক ব্যবহার করুন। মাস্ক ও গ্লাভস পরুন, লেবেলের মাত্রা মানুন এবং অপেক্ষার সময় শেষ হওয়ার আগে ফসল তুলবেন না।';

  @override
  String get notSureAskExpert => 'নিশ্চিত নন? বিশেষজ্ঞকে জিজ্ঞাসা করুন';

  @override
  String get listen => 'শুনুন';

  @override
  String get stopListening => 'থামুন';

  @override
  String get ttsUnavailable =>
      'এই ফোনে বাংলা ভয়েস নেই। Google Text-to-Speech অ্যাপে বাংলা ভাষা ইনস্টল করুন।';

  @override
  String get noProblemsForPart => 'এই অংশের কোনো সমস্যা তালিকায় নেই।';

  @override
  String stageDay(String n) {
    return '$nতম দিন';
  }

  @override
  String stageDaysBefore(String n) {
    return '$n দিন আগে';
  }

  @override
  String get calendarExplain => '০ দিন = যেদিন বীজ বপন বা চারা রোপণ করবেন।';

  @override
  String get addToMyCrops => 'আমার ফসলে যোগ করুন';

  @override
  String get noCalendar => 'এই ফসলের ক্যালেন্ডার এখনো যোগ করা হয়নি।';

  @override
  String get perBighaShort => 'প্রতি বিঘায়';

  @override
  String get calculateForMyLand => 'আমার জমির জন্য হিসাব করুন';

  @override
  String get noFertilizerData => 'এই ফসলের সার সুপারিশ এখনো যোগ করা হয়নি।';

  @override
  String get photoTapToLoad => 'ছবি দেখতে চাপুন';

  @override
  String get market => 'বাজার';

  @override
  String nearestMarket(String market) {
    return 'নিকটতম বাজার: $market';
  }

  @override
  String get wholesale => 'পাইকারি';

  @override
  String get retail => 'খুচরা';

  @override
  String pricesOfDate(String date) {
    return '$date তারিখের দর';
  }

  @override
  String get trendUp => 'বাড়ছে';

  @override
  String get trendDown => 'কমছে';

  @override
  String get trendStable => 'স্থির';

  @override
  String get unitKg => 'কেজি';

  @override
  String get unitMaund => 'মণ';

  @override
  String get unitPiece => 'পিস';

  @override
  String get unitHali => 'হালি';

  @override
  String get unitDozen => 'ডজন';

  @override
  String get unitLitre => 'লিটার';

  @override
  String perUnit(String unit) {
    return 'প্রতি $unit';
  }

  @override
  String get last30Days => 'গত ৩০ দিন';

  @override
  String get highest => 'সর্বোচ্চ';

  @override
  String get lowest => 'সর্বনিম্ন';

  @override
  String get average => 'গড়';

  @override
  String get demoDataWarning => 'এগুলো পরীক্ষার নমুনা দর – আসল বাজারদর নয়।';

  @override
  String get noPrices => 'এখনো কোনো দর পাওয়া যায়নি।';

  @override
  String source(String source) {
    return 'উৎস: $source';
  }

  @override
  String get priceTip =>
      'বিক্রির আগে কয়েকটি বাজারের দর মিলিয়ে নিন। ফসল তোলার সময় দাম কমলে কিছু অংশ সংরক্ষণ করে পরে বিক্রি করলে লাভ বেশি হতে পারে।';

  @override
  String get priceHistory => 'দামের ওঠানামা';

  @override
  String weatherIn(String district) {
    return '$district জেলার আবহাওয়া';
  }

  @override
  String get farmAdvice => 'কৃষি পরামর্শ';

  @override
  String get next7Days => 'আগামী ৭ দিন';

  @override
  String get humidity => 'আর্দ্রতা';

  @override
  String get rain => 'বৃষ্টি';

  @override
  String get wind => 'বাতাস';

  @override
  String get millimetre => 'মিমি';

  @override
  String get staleWeather =>
      'নতুন তথ্য আনা যায়নি – আগের পূর্বাভাস দেখানো হচ্ছে।';

  @override
  String forecastSource(String source) {
    return 'পূর্বাভাসের উৎস: $source';
  }

  @override
  String get chooseDistrictFirst =>
      'আবহাওয়া দেখতে সেটিংসে আপনার জেলা বেছে নিন।';

  @override
  String get wClear => 'পরিষ্কার আকাশ';

  @override
  String get wPartlyCloudy => 'আংশিক মেঘলা';

  @override
  String get wCloudy => 'মেঘলা';

  @override
  String get wFog => 'কুয়াশা';

  @override
  String get wDrizzle => 'গুঁড়ি গুঁড়ি বৃষ্টি';

  @override
  String get wRain => 'বৃষ্টি';

  @override
  String get wShowers => 'থেমে থেমে বৃষ্টি';

  @override
  String get wThunder => 'বজ্রসহ বৃষ্টি';

  @override
  String get tomorrow => 'আগামীকাল';

  @override
  String get myQuestions => 'আমার প্রশ্ন';

  @override
  String get communityAnswers => 'সবার জন্য উত্তর';

  @override
  String get askQuestion => 'প্রশ্ন করুন';

  @override
  String get questionHint => 'সমস্যাটি লিখুন: কোন ফসল, কী দেখছেন, কবে থেকে…';

  @override
  String get cropOptional => 'ফসল (ঐচ্ছিক)';

  @override
  String get noCropSelected => 'সাধারণ প্রশ্ন';

  @override
  String get addPhoto => 'ছবি দিন – এতে সমস্যা বুঝতে সুবিধা হয়';

  @override
  String get camera => 'ক্যামেরা';

  @override
  String get gallery => 'গ্যালারি';

  @override
  String get removePhoto => 'ছবি বাদ দিন';

  @override
  String get send => 'পাঠান';

  @override
  String get questionSent =>
      'প্রশ্ন পাঠানো হয়েছে। বিশেষজ্ঞ শীঘ্রই উত্তর দেবেন।';

  @override
  String get questionQueued =>
      'ইন্টারনেট নেই। প্রশ্নটি সংরক্ষিত আছে – সংযোগ পেলে নিজে থেকেই পাঠানো হবে।';

  @override
  String pendingUploads(String n) {
    return '$nটি প্রশ্ন পাঠানোর অপেক্ষায়';
  }

  @override
  String get sendNow => 'এখনই পাঠান';

  @override
  String get statusPending => 'উত্তরের অপেক্ষায়';

  @override
  String get statusAnswered => 'উত্তর দেওয়া হয়েছে';

  @override
  String get statusClosed => 'বন্ধ';

  @override
  String get expertAnswer => 'বিশেষজ্ঞের উত্তর';

  @override
  String get noQuestionsYet => 'আপনি এখনো কোনো প্রশ্ন করেননি।';

  @override
  String get noCommunityAnswers => 'এখনো কোনো উত্তর প্রকাশিত হয়নি।';

  @override
  String urgentCall(String number) {
    return 'জরুরি? $number নম্বরে কল করুন – বিনামূল্যে';
  }

  @override
  String get questionTooShort => 'আরেকটু বিস্তারিত লিখুন।';

  @override
  String get yourQuestion => 'আপনার প্রশ্ন';

  @override
  String get login => 'লগইন';

  @override
  String get register => 'নতুন অ্যাকাউন্ট';

  @override
  String get loginNeeded =>
      'বিশেষজ্ঞকে প্রশ্ন করতে লগইন করুন। এটি সম্পূর্ণ বিনামূল্যে।';

  @override
  String get yourName => 'আপনার নাম';

  @override
  String get mobileNumber => 'মোবাইল নম্বর';

  @override
  String get pin => 'পিন (৪–৬ সংখ্যা)';

  @override
  String get confirmPin => 'পিন আবার লিখুন';

  @override
  String get pinMismatch => 'দুটি পিন মেলেনি।';

  @override
  String get invalidPhone => 'সঠিক নম্বর দিন, যেমন ০১৭১২৩৪৫৬৭৮';

  @override
  String get invalidPin => 'পিন ৪ থেকে ৬ সংখ্যার হতে হবে।';

  @override
  String get requiredField => 'এটি পূরণ করুন';

  @override
  String get haveAccount => 'আগে অ্যাকাউন্ট খুলেছেন? লগইন করুন';

  @override
  String get noAccount => 'নতুন? অ্যাকাউন্ট খুলুন';

  @override
  String get logout => 'লগআউট';

  @override
  String loggedInAs(String name) {
    return '$name হিসেবে লগইন করা আছে';
  }

  @override
  String get pinHint => 'পিনটি মনে রাখুন, কাউকে বলবেন না।';

  @override
  String get myCropsEmpty =>
      'ফসল যোগ করলে কোন দিনে কী কাজ করতে হবে তার তালিকা পাবেন।';

  @override
  String get addCrop => 'ফসল যোগ করুন';

  @override
  String get chooseCrop => 'ফসল বেছে নিন';

  @override
  String get startDate => 'বপন বা চারা রোপণের তারিখ';

  @override
  String get plotName => 'জমির নাম (ঐচ্ছিক)';

  @override
  String get plotNameHint => 'যেমন: উত্তরের জমি, ছাদ';

  @override
  String get save => 'সংরক্ষণ করুন';

  @override
  String get delete => 'মুছুন';

  @override
  String get deleteConfirm => 'এই ফসল ও কাজের তালিকা মুছে ফেলবেন?';

  @override
  String get cancel => 'বাতিল';

  @override
  String cropDay(String n) {
    return '$nতম দিন';
  }

  @override
  String nextTask(String task) {
    return 'পরের কাজ: $task';
  }

  @override
  String get allTasksDone => 'সব কাজ শেষ';

  @override
  String get taskOverdue => 'সময় পেরিয়েছে';

  @override
  String get taskToday => 'আজ';

  @override
  String taskInDays(String n) {
    return '$n দিন পর';
  }

  @override
  String taskDaysAgo(String n) {
    return '$n দিন আগে';
  }

  @override
  String get markDone => 'সম্পন্ন হিসেবে চিহ্নিত করুন';

  @override
  String get noTasksThisWeek => 'এই সপ্তাহে কোনো কাজ নেই।';

  @override
  String get savedOnPhone => 'এই ফোনে সংরক্ষিত – ইন্টারনেট ছাড়াই চলে।';

  @override
  String get landSize => 'জমির পরিমাণ';

  @override
  String get unitDecimal => 'শতাংশ';

  @override
  String get unitKatha => 'কাঠা';

  @override
  String get unitBigha => 'বিঘা';

  @override
  String get unitAcre => 'একর';

  @override
  String get unitHectare => 'হেক্টর';

  @override
  String bighaNote(String n) {
    return 'এখানে ১ বিঘা = $n শতাংশ ধরা হয়েছে। আপনার এলাকায় ভিন্ন হলে সেটিংসে বদলান।';
  }

  @override
  String get fertilizerNeeded => 'প্রয়োজনীয় সার';

  @override
  String estimatedCost(String amount) {
    return 'সরকারি দামে খরচ: ৳$amount';
  }

  @override
  String get officialPrices => 'সরকার নির্ধারিত সারের দাম';

  @override
  String get officialPriceNote =>
      'ডিলার বেশি দাম চাইলে রশিদ চেয়ে নিন এবং উপজেলা কৃষি অফিসে জানান।';

  @override
  String get calcDisclaimer =>
      'মাঝারি উর্বর মাটির জন্য সাধারণ সুপারিশ। মাটি পরীক্ষা করে সার দিলে সবচেয়ে সঠিক হয়।';

  @override
  String kg(String n) {
    return '$n কেজি';
  }

  @override
  String gram(String n) {
    return '$n গ্রাম';
  }

  @override
  String perKg(String n) {
    return '৳$n/কেজি';
  }

  @override
  String get whenToApply => 'কখন দেবেন';

  @override
  String get enterLandSize => 'জমির পরিমাণ লিখুন';

  @override
  String sowInMonth(String month) {
    return '$month মাসে বপন/রোপণ করুন';
  }

  @override
  String harvestInMonth(String month) {
    return '$month মাসে ফসল তোলা';
  }

  @override
  String get rooftopOnly => 'শুধু ছাদে চাষযোগ্য';

  @override
  String get nothingThisMonth => 'এই মাসের জন্য কিছু তালিকায় নেই।';

  @override
  String get settings => 'সেটিংস';

  @override
  String get language => 'ভাষা';

  @override
  String get district => 'জেলা';

  @override
  String get iAmA => 'আমি একজন';

  @override
  String get textSize => 'লেখার আকার';

  @override
  String get textNormal => 'স্বাভাবিক';

  @override
  String get textLarge => 'বড়';

  @override
  String get textExtraLarge => 'আরও বড়';

  @override
  String get dataSaver => 'ডেটা সাশ্রয়';

  @override
  String get dataSaverDesc => 'ছবি শুধু চাপ দিলে লোড হবে';

  @override
  String get bighaSize => '১ বিঘা = কত শতাংশ?';

  @override
  String get updateNow => 'এখনই তথ্য হালনাগাদ করুন';

  @override
  String contentUpdated(String time) {
    return 'তথ্যভাণ্ডার হালনাগাদ: $time';
  }

  @override
  String get contentBuiltIn => 'অ্যাপের ভেতরের তথ্য ব্যবহার হচ্ছে';

  @override
  String get upToDate => 'তথ্য হালনাগাদ আছে।';

  @override
  String get serverAddress => 'সার্ভার ঠিকানা (উন্নত)';

  @override
  String get serverAddressHint =>
      'সার্ভারের API ঠিকানা দিন, যেমন https://api.example.com/api/v1। প্রকাশিত অ্যাপে https লাগবে; সাধারণ http (যেমন http://192.168.0.10:8000/api/v1) শুধু ডিবাগ বিল্ডে কাজ করে।';

  @override
  String get resetDefault => 'আগের অবস্থায় ফেরান';

  @override
  String get about => 'অ্যাপ সম্পর্কে';

  @override
  String get aboutText =>
      'কৃষি সাথী বাংলাদেশের কৃষক ও বাগানিদের ফসল, পোকা-রোগ, সার, আবহাওয়া ও বাজারদর নিয়ে সহজ পরামর্শ দেয়। পরামর্শগুলো সাধারণ – গুরুতর সমস্যায় উপজেলা কৃষি অফিসে যোগাযোগ করুন বা ১৬১২৩ নম্বরে কল করুন।';

  @override
  String version(String v) {
    return 'সংস্করণ $v';
  }

  @override
  String get account => 'অ্যাকাউন্ট';

  @override
  String get privacyPolicy => 'গোপনীয়তা নীতি';

  @override
  String get deleteAccount => 'অ্যাকাউন্ট মুছে ফেলুন';

  @override
  String get deleteAccountWarning =>
      'আপনার অ্যাকাউন্ট, সব প্রশ্ন, ছবি ও সেগুলোর উত্তর স্থায়ীভাবে মুছে যাবে। এটি আর ফেরত আনা যাবে না। এই ফোনে রাখা “আমার ফসল” থেকে যাবে।';

  @override
  String get deleteAccountPin => 'নিশ্চিত করতে আপনার পিন দিন';

  @override
  String get deleteAccountConfirm => 'স্থায়ীভাবে মুছুন';

  @override
  String get accountDeleted => 'আপনার অ্যাকাউন্ট মুছে ফেলা হয়েছে।';

  @override
  String get updateAppTitle => 'অ্যাপটি হালনাগাদ করুন';

  @override
  String get updateAppText =>
      'পুরোনো সংস্করণে কিছু সুবিধা (বাজারদর, প্রশ্ন) কাজ নাও করতে পারে। Google Play থেকে নতুন সংস্করণ নিন।';

  @override
  String get updateApp => 'হালনাগাদ';
}
