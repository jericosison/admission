<?php
// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PSAU Admission - Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f7f7f7;
        }
        .container {
            background: #fff;
            padding: 20px;
            max-width: 400px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input, button {
            display: block;
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            background-color: #28a745;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }
        #status {
            color: green;
            font-weight: bold;
        }
        #error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Mag-register - PSAU Admission</h2>
    <form id="registerForm" onsubmit="return false;">
        <input type="text" id="firstName" placeholder="First Name" required>
        <input type="text" id="lastName" placeholder="Last Name" required>
        <input type="email" id="email" placeholder="Email Address" required>
        <input type="text" id="phone" placeholder="+639123456789" required>
        <input type="password" id="password" placeholder="Password" required>
        <input type="password" id="confirmPassword" placeholder="Confirm Password" required>

        <div id="recaptcha-container"></div>

        <button type="button" id="sendOtpBtn" onclick="sendOTP()">Send OTP</button>
        <input type="text" id="otp" placeholder="Enter OTP Code" style="display: none;">
        <button type="button" id="verifyOtpBtn" onclick="verifyOTP()" style="display: none;">Verify OTP</button>

        <button type="submit" id="submitBtn" disabled style="display: none;">Isumite ang Registration</button>
    </form>
    <p id="status"></p>
    <p id="error"></p>
</div>

<script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/11.6.0/firebase-app.js";
    import { getAuth, RecaptchaVerifier, signInWithPhoneNumber } from "https://www.gstatic.com/firebasejs/11.6.0/firebase-auth.js";

    const firebaseConfig = {
        apiKey: "AIzaSyB7HqxV971vmWiJiXnWdaFnMaFx1C1t6s8",
        authDomain: "psau-admission-system.firebaseapp.com",
        projectId: "psau-admission-system",
        storageBucket: "psau-admission-system.appspot.com",
        messagingSenderId: "522448258958",
        appId: "1:522448258958:web:994b133a4f7b7f4c1b06df",
        measurementId: "G-F7G5P3KNSD"
    };

    const app = initializeApp(firebaseConfig);
    const auth = getAuth(app);

    let confirmationResult;

    // Setup reCAPTCHA
    window.recaptchaVerifier = new RecaptchaVerifier(auth, 'recaptcha-container', {
        size: 'normal',
        callback: (response) => {
            document.getElementById("status").innerText = "✅ reCAPTCHA verified.";
            document.getElementById("sendOtpBtn").disabled = false;
        },
        'expired-callback': () => {
            document.getElementById("status").innerText = "❌ reCAPTCHA expired.";
            document.getElementById("sendOtpBtn").disabled = true;
        }
    });

    // Send OTP
    window.sendOTP = async () => {
        const phone = document.getElementById("phone").value;
        const errorEl = document.getElementById("error");
        const statusEl = document.getElementById("status");

        if (!phone.startsWith("+63") || phone.length !== 13) {
            errorEl.innerText = "❌ Invalid PH number. Use +639XXXXXXXXX.";
            return;
        }

        document.getElementById("sendOtpBtn").disabled = true;
        statusEl.innerText = "Sending OTP...";

        try {
            confirmationResult = await signInWithPhoneNumber(auth, phone, window.recaptchaVerifier);
            statusEl.innerText = "✅ OTP sent to " + phone;
            errorEl.innerText = "";
            document.getElementById("otp").style.display = "block";
            document.getElementById("verifyOtpBtn").style.display = "block";
        } catch (error) {
            errorEl.innerText = "❌ Failed to send OTP: " + error.message;
            statusEl.innerText = "";
            document.getElementById("sendOtpBtn").disabled = false;
        }
    };

    // Verify OTP
    window.verifyOTP = async () => {
        const code = document.getElementById("otp").value;
        const errorEl = document.getElementById("error");
        const statusEl = document.getElementById("status");

        if (!confirmationResult) {
            errorEl.innerText = "❌ You must send OTP first.";
            return;
        }

        statusEl.innerText = "Verifying OTP...";
        try {
            await confirmationResult.confirm(code);
            statusEl.innerText = "✅ Phone number verified!";
            document.getElementById("submitBtn").disabled = false;
            document.getElementById("submitBtn").style.display = "block";
            document.getElementById("otp").style.display = "none";
            document.getElementById("verifyOtpBtn").style.display = "none";
        } catch (error) {
            errorEl.innerText = "❌ Incorrect OTP: " + error.message;
            statusEl.innerText = "";
        }
    };

    // Final submit
    document.getElementById("registerForm").addEventListener("submit", async function (e) {
        e.preventDefault();
        const firstName = document.getElementById("firstName").value;
        const lastName = document.getElementById("lastName").value;
        const email = document.getElementById("email").value;
        const phone = document.getElementById("phone").value;
        const pass = document.getElementById("password").value;
        const confirm = document.getElementById("confirmPassword").value;

        const errorEl = document.getElementById("error");
        const statusEl = document.getElementById("status");

        if (pass !== confirm) {
            errorEl.innerText = "❌ Passwords do not match.";
            return;
        }

        try {
            const res = await fetch("save_user.php", {
                method: "POST",
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    firstName, lastName, email, phone, pass
                })
            });
            const result = await res.json();
            if (result.success) {
                statusEl.innerText = result.message;
            } else {
                errorEl.innerText = result.message;
            }
        } catch (err) {
            errorEl.innerText = "❌ Error: " + err.message;
        }
    });
</script>
</body>
</html>
