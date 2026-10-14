@extends('public.layout')

@php
    $bn = app()->getLocale() === 'bn';
    $email = config('krishi.support_email');
    $privacyUrl = route('privacy', $bn ? [] : ['lang' => 'en']);
@endphp

@section('title', $bn ? 'অ্যাকাউন্ট মুছে ফেলুন' : 'Delete your account')

@section('content')
<div class="card"><div class="card-body p-4">
    <h1 class="h3">{{ $bn ? 'কৃষি সাথী অ্যাকাউন্ট মুছে ফেলুন' : 'Delete your Krishi Sathi account' }}</h1>

    @if (session('deleted'))
        <div class="alert alert-success mt-3" role="status">
            {{ $bn ? '✅ আপনার অ্যাকাউন্ট ও সব তথ্য মুছে ফেলা হয়েছে।' : '✅ Your account and all your data have been deleted.' }}
        </div>
    @else
        @if ($bn)
            <p>অ্যাকাউন্ট মুছলে নিচের সব কিছু <strong>সাথে সাথে স্থায়ীভাবে</strong> মুছে যাবে, ফেরত আনা যাবে না:</p>
            <ul>
                <li>আপনার নাম, মোবাইল নম্বর, জেলা ও পিন</li>
                <li>বিশেষজ্ঞকে পাঠানো সব প্রশ্ন, ছবি ও সেগুলোর উত্তর (কমিউনিটিতে প্রকাশিত হলেও)</li>
                <li>সব ফোনের সাইন-ইন</li>
            </ul>
            <p class="small text-muted">অ্যাপ থেকেও মোছা যায়: <strong>সেটিংস → অ্যাকাউন্ট মুছে ফেলুন</strong>। ফোনে রাখা “আমার ফসল” অ্যাপ আনইনস্টল করলে মুছে যায়।</p>
        @else
            <p>Deleting your account <strong>immediately and permanently</strong> removes the following. It cannot be undone:</p>
            <ul>
                <li>your name, mobile number, district and PIN</li>
                <li>all questions and photos you sent to experts, and their answers (also if published in the community)</li>
                <li>your sign-ins on every phone</li>
            </ul>
            <p class="small text-muted">You can also do this in the app: <strong>Settings → Delete account</strong>. “My crops” is stored only on your phone and is removed when you uninstall the app.</p>
        @endif

        <form method="POST" action="{{ route('account.delete') }}" class="mt-4" style="max-width: 420px">
            @csrf
            @unless ($bn)<input type="hidden" name="lang" value="en">@endunless
            <div class="mb-3">
                <label for="phone" class="form-label">{{ $bn ? 'মোবাইল নম্বর' : 'Mobile number' }}</label>
                <input id="phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" required maxlength="20"
                       value="{{ old('phone') }}" placeholder="01XXXXXXXXX"
                       class="form-control form-control-lg @error('phone') is-invalid @enderror">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="pin" class="form-label">{{ $bn ? 'পিন' : 'PIN' }}</label>
                <input id="pin" name="pin" type="password" inputmode="numeric" autocomplete="current-password" required maxlength="64"
                       class="form-control form-control-lg @error('pin') is-invalid @enderror">
                @error('pin')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-check mb-3">
                <input id="confirm" name="confirm" type="checkbox" value="1" required class="form-check-input @error('confirm') is-invalid @enderror">
                <label for="confirm" class="form-check-label">{{ $bn ? 'আমি বুঝেছি, আমার অ্যাকাউন্ট ও সব তথ্য স্থায়ীভাবে মুছে যাবে।' : 'I understand that my account and all my data will be permanently deleted.' }}</label>
                @error('confirm')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn btn-danger btn-lg w-100">{{ $bn ? 'অ্যাকাউন্ট মুছে ফেলুন' : 'Delete my account' }}</button>
        </form>

        <p class="small text-muted mt-4 mb-0">
            {{ $bn ? 'পিন ভুলে গেছেন?' : 'Forgot your PIN?' }}
            @if ($email)
                {{ $bn ? 'যে নম্বর দিয়ে অ্যাকাউন্ট খুলেছেন সেটি লিখে ইমেইল করুন:' : 'Email us with the mobile number you registered:' }}
                <a href="mailto:{{ $email }}?subject=Delete%20account">{{ $email }}</a>.
            @endif
            {{ $bn ? 'নম্বরটি আপনার কিনা যাচাই করে আমরা অ্যাকাউন্ট মুছে দেব।' : 'We will verify that the number is yours and delete the account.' }}
        </p>
    @endif

    <p class="small mt-3 mb-0"><a href="{{ $privacyUrl }}">{{ $bn ? 'গোপনীয়তা নীতি' : 'Privacy policy' }}</a></p>
</div></div>
@endsection
