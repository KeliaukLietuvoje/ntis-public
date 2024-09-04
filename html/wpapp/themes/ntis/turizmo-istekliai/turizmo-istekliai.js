(function ($) {
    'use strict';

    var turizmo_istekliai_view = getCookie('turizmo_istekliai_view');
    if (turizmo_istekliai_view == 'list') {
        $('.icon-grid').removeClass('active');
        $('.tic-place__places').removeClass('grid').addClass('list');
        $('.view-options button.icon-list').addClass('active');
    }
    else {
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
        }
        else if ($(this).hasClass('icon-list')) {
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

    // Trigger form submission when any input inside .content is changed
    $('#turizmo-istekliai-filter-form .content input').change(function(){
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
        $('.page-numbers').each(function(){
            let url = $(this).attr('href');
            
            if(typeof url != 'undefined'){
                $(this).attr('href',url.replace('limit=' + currentPage, 'limit=' + $('.page-select').val()));
            }
        });
        $(location).attr('href', filter_link);
    });

    const ntis_object_map = {
        init: function() {
            if(!document.getElementById('tic-place__map') || !maplibregl) return;
            const map = new maplibregl.Map({
                container: 'tic-place__map',
                style: 'https://basemap.startupgov.lt/vector/styles/bright/style.json',
                center: [23.8813, 55.1694],
                zoom: 8
            });
            const marker = new maplibregl.Marker()
                .setLngLat([23.8813, 55.1694])
                .addTo(map);
        }
    }
    document.addEventListener('DOMContentLoaded', () => {
        ntis_object_map.init();
    });
})(jQuery);