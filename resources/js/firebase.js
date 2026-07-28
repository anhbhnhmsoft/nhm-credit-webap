import { initializeApp } from 'firebase/app';
import { getAuth, RecaptchaVerifier, signInWithPhoneNumber, signOut } from 'firebase/auth';
import $ from 'jquery';

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

auth.languageCode = 'vi';

// debounce + mutex flags for safe re-rendering
window.__recaptchaRenderTimer = null;
window.__renderingRecaptcha = false;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Delay a bit to ensure DOM (and Livewire partials) are ready
    setTimeout(() => {
        render();
    }, 300);
    // setup resend timer if needed
    window.__otpCooldown = 0;
});

function render() {
    // Debounce rapid calls (tab toggle, rerenders)
    if (window.__recaptchaRenderTimer) {
        clearTimeout(window.__recaptchaRenderTimer);
    }
    window.__recaptchaRenderTimer = setTimeout(() => {
        doRender();
    }, 200);
}

function doRender() {
    if (window.__renderingRecaptcha) return;

    const container = document.getElementById('recaptcha-container');
    if (!container) {
        // Not on an OTP section or not visible in DOM
        return;
    }

    window.__renderingRecaptcha = true;
    try {
        // Clear any existing reCAPTCHA instance
        if (window.recaptchaVerifier && typeof window.recaptchaVerifier.clear === 'function') {
            try { window.recaptchaVerifier.clear(); } catch (e) {}
        }
        // Ensure container is empty before re-render
        try { container.innerHTML = ''; } catch (e) {}

        // Cấu hình reCAPTCHA - luôn dùng normal để hiển thị
        const recaptchaConfig = {
            size: 'normal', // Luôn hiển thị reCAPTCHA
            callback: () => {
                // User verified → allow sending
                $('#send_otp').prop('disabled', false);
            },
            'expired-callback': () => {
                // Expired → require verify again
                $('#send_otp').prop('disabled', true);
            },
        };

        window.recaptchaVerifier = new RecaptchaVerifier(auth, 'recaptcha-container', recaptchaConfig);

        window.recaptchaVerifier.render().then(() => {
            // Show container for visible reCAPTCHA
            $('#recaptcha-container').show();
            console.log('reCAPTCHA ready');
        }).catch((error) => {
            console.error('reCAPTCHA render error:', error);
        }).finally(() => {
            window.__renderingRecaptcha = false;
        });
    } catch (error) {
        console.error('reCAPTCHA initialization error:', error);
        window.__renderingRecaptcha = false;
    }
}


window.sendOTP = function () {
    // Visible reCAPTCHA: just ensure it exists and proceed; challenge is handled by widget
    if (!window.recaptchaVerifier) {
        alert('reCAPTCHA chưa sẵn sàng. Vui lòng thử lại sau ít giây.');
        return;
    }
    proceedWithOTP();
}

function proceedWithOTP() {
    var raw = $('#phoneNumber').val() || '';
    var phoneNumber = raw.replace(/\s+/g, '');
    // normalize VN phone: leading 0 -> +84
    if (/^0\d{9}$/.test(phoneNumber)) {
        phoneNumber = '+84' + phoneNumber.slice(1);
    }

    const appVerifier = window.recaptchaVerifier;
    const auth = getAuth();

    // check phone exists before sending OTP (login flow)
    if (window.location.pathname === '/login-otp') {
        $.post('/login-otp/check-phone', {
            phone: phoneNumber,
            _token: document.querySelector('input[name="_token"]').value,
        })
        .done(() => {
            sendWithFirebase(auth, phoneNumber, appVerifier)
                .then((confirmationResult) => onOtpSent(confirmationResult, phoneNumber))
                .catch(handleSendError);
        })
        .fail((xhr) => {
            alert(xhr.responseJSON?.message || 'Số điện thoại không tồn tại.');
        });
        return;
    }

    // check phone NOT exists before sending OTP (register flow)
    if (window.location.pathname === '/register-otp') {
        $.post('/register-otp/check-phone', {
            phone: phoneNumber,
            _token: document.querySelector('input[name="_token"]').value,
        })
        .done(() => {
            sendWithFirebase(auth, phoneNumber, appVerifier)
                .then((confirmationResult) => onOtpSent(confirmationResult, phoneNumber))
                .catch(handleSendError);
        })
        .fail((xhr) => {
            alert(xhr.responseJSON?.message || 'Số điện thoại đã tồn tại.');
        });
        return;
    }

    sendWithFirebase(auth, phoneNumber, appVerifier)
        .then((confirmationResult) => onOtpSent(confirmationResult, phoneNumber))
        .catch((error) => {
            handleSendError(error);
        });
}

function sendWithFirebase(auth, phoneNumber, appVerifier) {
    return signInWithPhoneNumber(auth, phoneNumber, appVerifier);
}

function onOtpSent(confirmationResult, phoneNumber) {
    window.confirmationResult = confirmationResult;
    alert('Đã gửi OTP. Vui lòng kiểm tra điện thoại.');
    $('#verification_input').show();
    $('#phone_input').hide();
    $('#recaptcha-container').hide();
    $('#login_btn').removeClass('hidden');
    $('#verify_btn').removeClass('hidden');
    // start cooldown 120s for resend label
    startOtpCooldown();
    // store normalized phone for server use
    window.__normalizedPhone = phoneNumber;
}

function handleSendError(error) {
    console.error('Firebase Send Error:', error);
    
    // Nếu là lỗi -39 hoặc lỗi không xác định, thử fallback
    if (error?.message?.includes('-39') || error?.code?.includes('-39')) {
        console.log('Detected error -39, trying fallback...');
        
        // Thử reload reCAPTCHA
        setTimeout(() => {
            render();
        }, 1000);
        
        alert('Đã xảy ra lỗi tạm thời. Vui lòng thử lại sau ít giây.');
        return;
    }
    
    const msg = mapFirebaseError(error);
    alert(msg);
}

window.verifyOTP = function () {
    const code = $("#verification_code").val();
    
    confirmationResult.confirm(code)
        .then((result) => {
            alert('Xác thực OTP thành công.');
            const user = result.user;
            if (window.location.pathname === '/register-otp') {
                const phone = $('#phoneNumber').val();
                const password = $('#password').val();
                const confirmPassword = $('#confirmPassword').val();
                $.post('/register-otp', {
                    phone: phone,
                    password: password,
                    confirm_password: confirmPassword,
                    _token: document.querySelector('input[name="_token"]').value,
                })
                .done((res) => {
                    if (res && res.redirect) {
                        window.location.href = res.redirect;
                    } else {
                        window.location.href = '/';
                    }
                })
                .fail((xhr) => {
                    alert('Đăng ký thất bại: ' + (xhr.responseJSON?.message || 'Lỗi không xác định'));
                });
            } else {
                // login flow after OTP
                const phone = window.__normalizedPhone || $('#phoneNumber').val();
                $.post('/login-otp', {
                    phone: phone,
                    _token: document.querySelector('input[name="_token"]').value,
                })
                .done((res) => {
                    window.location.href = res?.redirect || '/';
                })
                .fail((xhr) => {
                    alert('Đăng nhập thất bại: ' + (xhr.responseJSON?.message || 'Lỗi không xác định'));
                });
            }
        }).catch((error) => {
            const msg = mapFirebaseError(error);
            alert(msg);
            $('#verification_code').focus();
        });
}

function startOtpCooldown() {
    window.__otpCooldown = 120;
    updateSendOtpButton();
    const timer = setInterval(() => {
        window.__otpCooldown--;
        updateSendOtpButton();
        if (window.__otpCooldown <= 0) {
            clearInterval(timer);
            $('#send_otp').prop('disabled', false).text('Gửi lại OTP');
        }
    }, 1000);
}

function updateSendOtpButton() {
    if (window.__otpCooldown > 0) {
        $('#send_otp').prop('disabled', true).text('Gửi lại OTP (' + window.__otpCooldown + 's)');
    }
}

window.tryAgain = function () {
    $('#verification_input').hide();
    $('#phone_input').show();
    $('#verification_code').val('');
    render();
}

window.signOut = function () {
    const auth = getAuth();
    signOut(auth)
        .then(() => {
            alert('Đăng xuất thành công.');
        }).catch((error) => {
            alert('Đã xảy ra lỗi khi đăng xuất: ' + (error?.message || 'Không xác định'));
        });
}

export { auth, RecaptchaVerifier, signInWithPhoneNumber };

function mapFirebaseError(error) {
    const code = (error && error.code) || '';
    switch (code) {
        case 'auth/invalid-verification-code':
            return 'Mã OTP không đúng. Vui lòng kiểm tra và thử lại.';
        case 'auth/missing-verification-code':
            return 'Bạn chưa nhập mã OTP. Vui lòng nhập mã và thử lại.';
        case 'auth/code-expired':
        case 'auth/session-expired':
            return 'Mã OTP đã hết hạn. Vui lòng gửi lại OTP.';
        case 'auth/too-many-requests':
            return 'Bạn thao tác quá nhanh. Vui lòng thử lại sau ít phút.';
        case 'auth/captcha-check-failed':
            return 'Xác minh reCAPTCHA không hợp lệ. Vui lòng thử lại.';
        case 'auth/invalid-phone-number':
            return 'Số điện thoại không hợp lệ. Vui lòng kiểm tra lại.';
        case 'auth/quota-exceeded':
            return 'Đã vượt quá hạn mức gửi OTP. Vui lòng thử lại sau.';
        case 'auth/network-request-failed':
            return 'Không thể kết nối mạng. Vui lòng kiểm tra Internet và thử lại.';
        default:
            return error?.message || 'Đã xảy ra lỗi. Vui lòng thử lại.';
    }
}
