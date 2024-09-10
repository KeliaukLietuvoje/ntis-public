(function ($) {
    'use strict';

    var turizmo_istekliai_view = getCookie('turizmo_istekliai_view');
    if (turizmo_istekliai_view == 'list') {
        $('.icon-grid').removeClass('active');
        $('.tic-place__places').removeClass('grid').addClass('list');
        $('.view-options button.icon-list').addClass('active');
    } else {
        $('.icon-list').removeClass('active');
        $('.tic-place__places').removeClass('list').addClass('grid');
        $('.view-options button.icon-grid').addClass('active');
    }

    $('.view-options button').on('click', function (e) {

        if ($(this).hasClass('icon-grid')) {
            $('.icon-list').removeClass('active');
            $('.tic-place__places').removeClass('list').addClass('grid');
            $(this).addClass('active');
            setCookie('turizmo_istekliai_view', 'grid', 365);
        } else if ($(this).hasClass('icon-list')) {
            $('.icon-grid').removeClass('active');
            $('.tic-place__places').removeClass('grid').addClass('list');
            $(this).addClass('active');
            setCookie('turizmo_istekliai_view', 'list', 365);
        }
    });

    function setCookie(key, value, expiry) {
        var expires = new Date();
        expires.setTime(expires.getTime() + (expiry * 24 * 60 * 60 * 1000));
        document.cookie = key + '=' + value + ';path=/' + ';expires=' + expires.toUTCString();
    }

    function getCookie(key) {
        var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
        return keyValue ? keyValue[2] : null;
    }

    function checkboxChanged() {
        var $this = $(this),
            checked = $this.prop("checked"),
            container = $this.parent();

        container.find('input[type="checkbox"]')
            .prop({
                indeterminate: false,
                checked: checked
            })
            .siblings('label')
            .removeClass('custom-checked custom-unchecked custom-indeterminate')
            .addClass(checked ? 'custom-checked' : 'custom-unchecked');

        checkSiblings(container, checked);
    }

    function checkSiblings($el, checked) {
        var parent = $el.parent().parent(),
            all = true,
            indeterminate = false;

        $el.siblings().each(function () {
            return all = ($(this).children('input[type="checkbox"]').prop("checked") === checked);
        });

        if (all && checked) {
            parent.children('input[type="checkbox"]')
                .prop({
                    indeterminate: false,
                    checked: checked
                })
                .siblings('label')
                .removeClass('custom-checked custom-unchecked custom-indeterminate')
                .addClass(checked ? 'custom-checked' : 'custom-unchecked');

            checkSiblings(parent, checked);
        } else if (all && !checked) {
            indeterminate = parent.find('input[type="checkbox"]:checked').length > 0;

            parent.children('input[type="checkbox"]')
                .prop("checked", checked)
                .prop("indeterminate", indeterminate)
                .siblings('label')
                .removeClass('custom-checked custom-unchecked custom-indeterminate')
                .addClass(indeterminate ? 'custom-indeterminate' : (checked ? 'custom-checked' : 'custom-unchecked'));

            checkSiblings(parent, checked);
        } else {
            $el.parents("li").children('input[type="checkbox"]')
                .prop({
                    indeterminate: true,
                    checked: false
                })
                .siblings('label')
                .removeClass('custom-checked custom-unchecked custom-indeterminate')
                .addClass('custom-indeterminate');
        }
    }

    // Trigger form submission when any input inside .content is changed
    $('#turizmo-istekliai-filter-form .content input[type=checkbox]').change(function () {
        checkboxChanged.call(this);
        $('#turizmo-istekliai-filter-form').trigger('submit');
    });

    $('.page-select').change(function () {
        let filter_link;
        let currentUrl = window.location.href;
        if (currentUrl.indexOf('?') > -1) {
            if (currentUrl.indexOf('limit=') > -1) {
                var currentPage = currentUrl.split('limit=')[1].split('&')[0];
                filter_link = currentUrl.replace('limit=' + currentPage, 'limit=' + $('.page-select').val());
            } else {
                filter_link = currentUrl + '&limit=' + $('.page-select').val();
            }
        } else {
            filter_link = currentUrl + '?limit=' + $('.page-select').val();
        }
        $('.page-numbers').each(function () {
            let url = $(this).attr('href');

            if (typeof url != 'undefined') {
                $(this).attr('href', url.replace('limit=' + currentPage, 'limit=' + $('.page-select').val()));
            }
        });
        $(location).attr('href', filter_link);
    });
    

    var swiper = new Swiper('.tic-swiper', {
        cssMode: true,
        slidesPerView: 3,
        spaceBetween: 16,
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        pagination: {
          el: '.swiper-pagination',
          clickable: true,
        },
    });

    const ntis_object_map = {
        init: function () {
            if (!document.getElementById('tic-place__map') || !maplibregl) return;

            const mapElement = document.querySelector('.tic-place__map');
            const lat = parseFloat(mapElement.getAttribute('data-lat'));
            const lng = parseFloat(mapElement.getAttribute('data-lng'));
            const map = new maplibregl.Map({
                container: 'tic-place__map',
                style: 'https://basemap.startupgov.lt/vector/styles/bright/style.json',
                center: [lng, lat],
                zoom: objVars.map.zoom,
                attributionControl: false
            });
            map.addControl(new maplibregl.AttributionControl({
                compact: true
            }));


            const markerDiv = document.createElement('div');
            markerDiv.className = 'custom-marker';
            markerDiv.style.backgroundImage = `url(${objVars.map.ico})`;
            markerDiv.style.backgroundSize = 'contain';
            markerDiv.style.width = `${objVars.map.ico_width}px`;
            markerDiv.style.height = `${objVars.map.ico_height}px`;

            const marker = new maplibregl.Marker({
                    element: markerDiv
                })
                .setLngLat([lng, lat])
                .addTo(map);
        }
    }
    document.addEventListener('DOMContentLoaded', () => {
        ntis_object_map.init();
    });
})(jQuery);