import $ from 'jquery';

window.loginWithPhone = function () {
    const btn = $('#login_phone_btn');
    let phone = $('#phoneNumber').val() || '';
    phone = phone.replace(/\s+/g, '');

    if (!phone) {
        alert('Vui lòng nhập số điện thoại');
        return;
    }

    const csrf = document.querySelector('input[name="_token"]')?.value || '';

    btn.prop('disabled', true).text('Đang xử lý...').addClass('cursor-wait opacity-80');

    $.ajax({
        url: '/login-otp',
        method: 'POST',
        data: {
            phone,
            _token: csrf,
        },
    })
        .done((res) => {
            window.location.href = res?.redirect || '/';
        })
        .fail((xhr) => {
            const msg = xhr?.responseJSON?.message || 'Đăng nhập thất bại. Vui lòng thử lại.';
            alert(msg);
        })
        .always(() => {
            btn.prop('disabled', false).text('Đăng nhập').removeClass('cursor-wait opacity-80');
        });
};

document.addEventListener('DOMContentLoaded', () => {
});

export {};

