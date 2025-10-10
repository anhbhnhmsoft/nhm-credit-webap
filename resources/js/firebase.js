import { initializeApp } from 'firebase/app';
import { getAuth, RecaptchaVerifier, signInWithPhoneNumber, signOut } from 'firebase/auth';

const ENV_VARS =import.meta.env;

const firebaseConfig = {
    apiKey: ENV_VARS.VITE_FIREBASE_API_KEY,
    authDomain: ENV_VARS.VITE_FIREBASE_AUTH_DOMAIN,
    projectId: ENV_VARS.VITE_FIREBASE_PROJECT_ID,
    storageBucket: ENV_VARS.VITE_FIREBASE_STORAGE_BUCKET,
    messagingSenderId: ENV_VARS.VITE_FIREBASE_MESSAGING_SENDER_ID,
    appId: ENV_VARS.VITE_FIREBASE_APP_ID
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);

auth.languageCode = 'en';

window.onload = function () {
    render();
}

function render() {
    window.recaptchaVerifier = new RecaptchaVerifier(auth, 'recaptcha-container', {
        'size': 'normal',
        'callback': (response) => {
            alert('Recaptcha verified!!! Please enter your phone number!!!')
            $('#send_otp').removeAttr('disabled');
        },
        'expired-callback': () => {
            alert('Recaptcha expired!!! Please try again!!!');
        }
    });

recaptchaVerifier.render();
}


window.sendOTP = function () {
    var phoneNumber = $('#phoneNumber').val();

    const appVerifier = window.recaptchaVerifier;

    const auth = getAuth();

    signInWithPhoneNumber(auth, phoneNumber, appVerifier)
        .then((confirmationResult) => {
            window.confirmationResult = confirmationResult;
            alert('OTP sent successfully!!! Please check your phone!!!');
            $('#verification_input').show();
            $('#phone_input').hide();
        })
        .catch((error) => {
            window.recaptchaVerifier.render().then(function (widgetId) {
                grecaptcha.reset(widgetId);
            });
            alert(error.message);
        });
}

window.verifyOTP = function () {
    const code = $("#verification_code").val();
    
    confirmationResult.confirm(code)
        .then((result) => {
            alert('Code verified successfully!!!');
            const user = result.user;
            $('#verification_input').hide();
            $('#verification_code').val('');
            $('#phone_input').show();
        }).catch((error) => {
            alert(error.message);
        });
}

window.tryAgain = function () {
    $('#verification_input').hide();
    $('#phone_input').show();
    $('#verification_code').val('');
}

window.signOut = function () {
    const auth = getAuth();
    signOut(auth)
        .then(() => {
            alert('Logout successful!!!');
        }).catch((error) => {
            alert(error.message);
        });
}

export { auth, RecaptchaVerifier, signInWithPhoneNumber };
