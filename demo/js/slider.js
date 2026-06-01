document.addEventListener('DOMContentLoaded', function () {
    var track = document.querySelector('.slider-track');
    if (!track) return;

    var slides = track.querySelectorAll('.slide');
    var total = slides.length;
    var current = 0;
    var timer;

    function goTo(index) {
        if (index < 0) index = total - 1;
        if (index >= total) index = 0;
        current = index;
        track.style.transform = 'translateX(-' + (current * 100) + '%)';

        var dots = document.querySelectorAll('.slider-dots span');
        for (var i = 0; i < dots.length; i++) {
            dots[i].classList.toggle('active', i === current);
        }
    }

    function next() { goTo(current + 1); }
    function prev() { goTo(current - 1); }

    function startAuto() {
        timer = setInterval(next, 3000);
    }
    function stopAuto() {
        clearInterval(timer);
    }

    var btnPrev = document.querySelector('.slider-btn.prev');
    var btnNext = document.querySelector('.slider-btn.next');
    if (btnPrev) btnPrev.addEventListener('click', function () { stopAuto(); prev(); startAuto(); });
    if (btnNext) btnNext.addEventListener('click', function () { stopAuto(); next(); startAuto(); });

    var dots = document.querySelectorAll('.slider-dots span');
    for (var d = 0; d < dots.length; d++) {
        (function (idx) {
            dots[idx].addEventListener('click', function () {
                stopAuto();
                goTo(idx);
                startAuto();
            });
        })(d);
    }

    goTo(0);
    startAuto();
});
