@extends('public.layout')

@php
    $bn = app()->getLocale() === 'bn';
    $operator = config('krishi.operator_name');
    $email = config('krishi.support_email');
    $updated = \Illuminate\Support\Carbon::parse(config('krishi.privacy_policy_updated'));
    $deleteUrl = route('account.delete', $bn ? [] : ['lang' => 'en']);
@endphp

@section('title', $bn ? 'গোপনীয়তা নীতি' : 'Privacy policy')

@section('content')
<div class="card"><div class="card-body p-4">
@if ($bn)
    <h1 class="h3">গোপনীয়তা নীতি</h1>
    <p class="text-muted small">সর্বশেষ হালনাগাদ: {{ \App\Support\BanglaText::toBanglaDigits($updated->format('d/m/Y')) }}</p>

    <p>“কৃষি সাথী” অ্যাপটি পরিচালনা করে <strong>{{ $operator }}</strong>। আমরা যত কম সম্ভব তথ্য নিই, এবং সেটা শুধু আপনাকে কৃষি সেবা দেওয়ার জন্য ব্যবহার করি। আমরা কোনো তথ্য বিক্রি করি না, অ্যাপে কোনো বিজ্ঞাপন বা ট্র্যাকার নেই।</p>

    <h2>১. অ্যাকাউন্ট ছাড়া ব্যবহার</h2>
    <p>ফসলের তথ্য, বাজারদর, আবহাওয়া ও সতর্কবার্তা দেখতে কোনো অ্যাকাউন্ট লাগে না। এ সময় অ্যাপ শুধু আপনার <strong>বেছে নেওয়া জেলা</strong> ও ভাষা সার্ভারে পাঠায়, যাতে সঠিক আবহাওয়া ও দাম দেখানো যায়। অ্যাপ আপনার GPS লোকেশন নেয় না।</p>

    <h2>২. অ্যাকাউন্ট খুললে যে তথ্য রাখা হয়</h2>
    <ul>
        <li>নাম, মোবাইল নম্বর, জেলা, আপনি কে (কৃষক, ছাদবাগানি …) এবং ভাষা।</li>
        <li>পিন – শুধু এনক্রিপ্ট করা (hash) রূপে রাখা হয়; কেউ তা পড়তে পারে না।</li>
        <li>শেষ লগইনের সময়।</li>
    </ul>

    <h2>৩. বিশেষজ্ঞকে প্রশ্ন</h2>
    <p>আপনি যে প্রশ্ন, ফসলের নাম ও ছবি পাঠান, তা সার্ভারে রাখা হয়। ছবি ছোট করা হয় এবং ছবির ভেতরের লুকানো তথ্য (যেমন কোথায় তোলা হয়েছে) মুছে ফেলা হয়। ক্যামেরা বা গ্যালারি শুধু তখনই খোলে যখন আপনি নিজে “ছবি যোগ করুন” চাপেন।</p>
    <p>উত্তর দেওয়ার জন্য আমাদের কৃষি বিশেষজ্ঞ ও অ্যাডমিনরা আপনার নাম, মোবাইল নম্বর ও জেলা দেখতে পারেন (দরকার হলে ফোন করে বিস্তারিত জানতে)। কোনো প্রশ্ন-উত্তর সবার কাজে লাগলে বিশেষজ্ঞ সেটি “কমিউনিটি”-তে প্রকাশ করতে পারেন – সেখানে প্রশ্ন, ছবি, উত্তর ও শুধু জেলার নাম দেখা যায়; <strong>আপনার নাম বা নম্বর কখনো দেখানো হয় না</strong>।</p>

    <p><strong>AI ডাক্তার:</strong> AI ডাক্তার ব্যবহার করলে আপনার ছবি ও প্রশ্ন (নাম বা মোবাইল নম্বর নয়) উত্তর তৈরির জন্য Anthropic-এর কাছে পাঠানো হয় – তারা Claude নামের AI তৈরি করে এবং আমাদের পক্ষ থেকে কাজটি করে। তাদের বাণিজ্যিক শর্ত অনুযায়ী এই তথ্য দিয়ে তারা নিজেদের AI প্রশিক্ষণ দেয় না, আর নিরাপত্তা যাচাইয়ের জন্য অল্প সময় রাখে। কথোপকথনটি আমরা রাখি, যাতে আপনি পরে দেখতে পারেন এবং আমাদের বিশেষজ্ঞরা উত্তরের মান যাচাই করতে পারেন। কথা বলে প্রশ্ন করলে ফোনের নিজস্ব ভয়েস সেবা (যেমন Google) কথাকে লেখায় রূপান্তর করে; আমরা শুধু লেখাটি পাই, কোনো অডিও নয়।</p>

    <h2>৪. শুধু আপনার ফোনে থাকে</h2>
    <p>“আমার ফসল” (ফসল, তারিখ, জমির পরিমাণ), সেটিংস, সংরক্ষিত তথ্য এবং ইন্টারনেট না থাকায় এখনো না-পাঠানো প্রশ্ন শুধু আপনার ফোনেই থাকে। অ্যাপ মুছে ফেললে এগুলোও মুছে যায়। লেখা পড়ে শোনানো হয় ফোনের নিজস্ব ভয়েস ইঞ্জিন দিয়ে।</p>

    <h2>৫. অন্য কারো সাথে শেয়ার</h2>
    <p>আমরা আপনার ব্যক্তিগত তথ্য কাউকে বিক্রি বা শেয়ার করি না (উপরের AI ডাক্তার সেবা ছাড়া, যা শুধু আমাদের হয়ে কাজ করে)। আবহাওয়ার পূর্বাভাস আমাদের সার্ভার জেলার অবস্থান দিয়ে Open-Meteo থেকে আনে – তাতে আপনার কোনো ব্যক্তিগত তথ্য যায় না। আইন অনুযায়ী বাধ্য হলে শুধু তখনই কর্তৃপক্ষকে তথ্য দেওয়া হতে পারে।</p>

    <h2>৬. নিরাপত্তা</h2>
    <p>অ্যাপ ও সার্ভারের মধ্যে সব তথ্য এনক্রিপ্টেড (HTTPS) সংযোগে যায়। ভুল পিন বারবার দিলে অ্যাকাউন্ট কিছুক্ষণের জন্য বন্ধ থাকে। সাইন-ইন মেয়াদ {{ \App\Support\BanglaText::toBanglaDigits((string) config('krishi.token_ttl_days')) }} দিন পর শেষ হয়।</p>

    <h2>৭. কতদিন রাখা হয় ও অ্যাকাউন্ট মুছে ফেলা</h2>
    <p>আপনি অ্যাকাউন্ট মুছে না ফেলা পর্যন্ত তথ্য রাখা হয়। অ্যাকাউন্ট মুছলে আপনার প্রোফাইল, সব প্রশ্ন, ছবি ও সেগুলোর উত্তর <strong>সাথে সাথে স্থায়ীভাবে মুছে যায়</strong> (সার্ভারের ব্যাকআপ কপি মেয়াদ শেষে মুছে যায়)। দুইভাবে মুছতে পারেন:</p>
    <ul>
        <li>অ্যাপে: <strong>সেটিংস → অ্যাকাউন্ট মুছে ফেলুন</strong></li>
        <li>ওয়েবে: <a href="{{ $deleteUrl }}">{{ $deleteUrl }}</a></li>
    </ul>
    <p>পিন ভুলে গেলে নিচের ঠিকানায় যোগাযোগ করুন; আমরা নম্বরটি আপনার কিনা যাচাই করে মুছে দেব।</p>

    <h2>৮. শিশুরা</h2>
    <p>অ্যাপটি কৃষক ও বাগানপ্রেমীদের জন্য তৈরি, শিশুদের জন্য নয়। ১৩ বছরের কম বয়সী কারো অ্যাকাউন্ট খোলা উচিত নয়।</p>

    <h2>৯. যোগাযোগ ও পরিবর্তন</h2>
    <p>প্রশ্ন বা অভিযোগ থাকলে যোগাযোগ করুন: <strong>{{ $operator }}</strong>@if ($email) – <a href="mailto:{{ $email }}">{{ $email }}</a>@endif। নীতিতে বড় কোনো পরিবর্তন হলে অ্যাপে জানানো হবে এবং এই পাতার তারিখ বদলাবে।</p>
@else
    <h1 class="h3">Privacy policy</h1>
    <p class="text-muted small">Last updated: {{ $updated->format('j F Y') }}</p>

    <p>The Krishi Sathi app is operated by <strong>{{ $operator }}</strong>. We collect as little as possible and use it only to provide farming services to you. We never sell data, and the app contains no advertising or third-party trackers.</p>

    <h2>1. Using the app without an account</h2>
    <p>Crop information, market prices, weather and alerts work without an account. The app then sends only the <strong>district you chose</strong> and your language to our server, so it can show the right weather and prices. The app does not use GPS location.</p>

    <h2>2. If you create an account</h2>
    <ul>
        <li>Name, mobile number, district, user type (farmer, rooftop gardener …) and language.</li>
        <li>Your PIN – stored only as a one-way hash; nobody can read it.</li>
        <li>The time you last signed in.</li>
    </ul>

    <h2>3. Questions to experts</h2>
    <p>The questions, crop and photos you send are stored on our server. Photos are resized and their hidden metadata (such as where they were taken) is removed. The camera or gallery opens only when you tap “Add photo”.</p>
    <p>To answer you, our agriculture experts and administrators can see your name, mobile number and district (to call you if more detail is needed). If a question and answer would help others, an expert may publish it in the community section, showing the question, photo, answer and only your district – <strong>never your name or number</strong>.</p>

    <p><strong>AI doctor:</strong> when you use the AI doctor, your photo and question (not your name or phone number) are sent to Anthropic, the company that makes the Claude AI, which produces the answer on our behalf. Under its commercial terms Anthropic does not use this data to train its AI and keeps it only for a limited time for safety checks. We keep the conversation so you can see it again and so our experts can check the quality of the answers. If you ask by voice, your phone's own speech service (e.g. Google) turns speech into text; we receive only the text, never audio.</p>

    <h2>4. Kept only on your phone</h2>
    <p>“My crops” (crop, dates, land size), settings, saved content and questions not yet sent because you were offline stay on your phone only, and are removed when you uninstall the app. Read-aloud uses your phone’s own text-to-speech engine.</p>

    <h2>5. Sharing</h2>
    <p>We do not sell or share your personal data (apart from the AI doctor service above, which works only on our behalf). Our server fetches weather forecasts from Open-Meteo using the district’s location only – no personal data is sent. We disclose data to authorities only when the law requires it.</p>

    <h2>6. Security</h2>
    <p>All data between the app and the server travels over an encrypted (HTTPS) connection. Repeated wrong PINs lock the account for a while. Sign-ins expire after {{ config('krishi.token_ttl_days') }} days.</p>

    <h2>7. Retention and deleting your account</h2>
    <p>We keep your data until you delete your account. Deleting it <strong>immediately and permanently</strong> removes your profile, all your questions, photos and their answers (copies in server backups are removed when those backups expire). You can delete your account:</p>
    <ul>
        <li>in the app: <strong>Settings → Delete account</strong></li>
        <li>on the web: <a href="{{ $deleteUrl }}">{{ $deleteUrl }}</a></li>
    </ul>
    <p>If you forgot your PIN, contact us at the address below; we will verify that the number is yours and delete the account.</p>

    <h2>8. Children</h2>
    <p>The app is made for farmers and gardeners and is not directed at children. People under 13 should not create an account.</p>

    <h2>9. Contact and changes</h2>
    <p>Questions or complaints: <strong>{{ $operator }}</strong>@if ($email) – <a href="mailto:{{ $email }}">{{ $email }}</a>@endif. If this policy changes significantly we will tell you in the app and update the date on this page.</p>
@endif
</div></div>
@endsection
