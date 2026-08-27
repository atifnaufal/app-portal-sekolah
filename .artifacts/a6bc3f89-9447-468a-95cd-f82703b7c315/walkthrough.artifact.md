# Walkthrough - Advanced Account Recovery

I have implemented a comprehensive account recovery system that allows users to retrieve forgotten emails or reset passwords, while providing clear manual recovery instructions.

## Changes Made

### 1. Multi-Channel Recovery Logic
- **Find My Email**: Users can now recover their registered email address by providing their **NIK** and **Phone Number**. If a match is found, the email is displayed securely.
- **Forgot Password flow**: Added a dedicated page for requesting password reset links via email.
- **Manual Admin Recovery**: Integrated a mandatory instruction across all login/recovery pages: *"Berikan NIK ke Bagian Admin IT Sekolah untuk reset atau aktivasi."*

### 2. High-End Recovery UI
- **Forgot Email View**: Created a clean, mobile-first interface for NIK/Phone verification.
- **Forgot Password View**: Created an intuitive interface for email-based reset requests.
- **Login Page Integration**: Added quick links for "Lupa Password?" and "Lupa Email?" directly below the password field for better accessibility.

### 3. Verification & Security
- **Data Matching**: The "Find Email" feature uses a strict double-match (NIK + No HP) to ensure privacy.
- **Visual Cues**: Used warning/info icons and distinct color boxes to highlight important instructions.

## Verification Results
- **Email Recovery**: Verified that inputting a valid NIK and Phone Number successfully returns the associated email.
- **Navigation**: Verified that all recovery pages link back to the login screen smoothly.
- **Instruction Visibility**: Confirmed the NIK manual recovery notice is highly visible and professionally styled.
