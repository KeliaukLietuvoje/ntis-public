(function ($) {
    'use strict';

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

    const ntis_tags = {
        init() {
            document.querySelectorAll('.tags-wrapper').forEach(wrapper => {
                const tags = Array.from(wrapper.querySelectorAll('.tag'));
                const moreButton = wrapper.querySelector('.more-button');
                
                // Remove all inline styles from tags on init
                tags.forEach(tag => tag.removeAttribute('style'));
                
                if (moreButton) {
                    moreButton.addEventListener('click', () => this.showAllTags(wrapper, tags, moreButton));
                }
                
                const adjustVisibility = () => this.adjustTagVisibility(wrapper, tags, moreButton);
                window.addEventListener('resize', adjustVisibility);
                adjustVisibility();
            });
        },
    
        showAllTags(wrapper, tags, moreButton) {
            tags.forEach(tag => {
                tag.style.display = 'inline-flex';
                tag.style.position = 'relative';
            });
            moreButton.style.display = 'none';
            wrapper.style.flexWrap = 'wrap';
        },
    
        adjustTagVisibility(wrapper, tags, moreButton) {
            console.log('adjusting visibility');
            const wrapperWidth = wrapper.offsetWidth;
            let currentWidth = 0;
            let isOverflowing = false;
    
            tags.forEach(tag => {
                tag.style.display = 'inline-flex';
                tag.style.position = 'relative';
                currentWidth += tag.offsetWidth + 8;
    
                if (currentWidth + (moreButton?.offsetWidth || 0) > wrapperWidth) {
                    tag.style.display = 'none';
                    tag.style.position = 'absolute';
                    isOverflowing = true;
                }
            });
    
            if (moreButton) {
                moreButton.style.display = isOverflowing ? 'inline-flex' : 'none';
            }
        }
    };
    

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

    var paged = null;
    let typingTimer;
    const debounceTime = 500; 
    $('#filter-title').on('input', function() {
        clearTimeout(typingTimer); 
        typingTimer = setTimeout(function() {
            paged = 1;
            $('#turizmo-istekliai-filter-form').trigger('submit');
        }, debounceTime);
    });

    $('.view-options button').on('click', function (e) {

        // Clear all styles from tags before switching views
        $('.tic-place__places .tag').removeAttr('style');
    
        if ($(this).hasClass('icon-grid')) {
            $('.icon-list').removeClass('active');
            $('.tic-place__places').removeClass('list').addClass('grid');
            $(this).addClass('active');
            setCookie('turizmo_istekliai_view', 'grid', 365);
            
            turizmo_istekliai_view = 'grid';
    
        } else if ($(this).hasClass('icon-list')) {
            $('.icon-grid').removeClass('active');
            $('.tic-place__places').removeClass('grid').addClass('list');
            $(this).addClass('active');
            setCookie('turizmo_istekliai_view', 'list', 365);

            turizmo_istekliai_view = 'list';
        }
    
        // Reinitialize tags to adjust visibility properly
        ntis_tags.init();
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

    $('#turizmo-istekliai-filter-form .content input[type=checkbox]').change(function () {
        checkboxChanged.call(this);
        paged = 1;
        $('#turizmo-istekliai-filter-form').trigger('submit');
    });
    $('#turizmo-istekliai-filter-form').submit(function (e) {
        e.preventDefault();
        var form = $(this);
        var formData = form.serialize();

        if(paged) {
            formData+='&paged='+paged;
            formData+='&view='+turizmo_istekliai_view;
        }   
        $.ajax({
            url: objVars.ajaxurl,
            data: formData,
            method: 'POST',
            beforeSend: function() {
                $('#tic-wrapper').fadeOut('fast'); // Fade out before sending the request
            },
            success: function(response) {
                if (response.success) {
                    $('#tic-wrapper').html(response.data.html).fadeIn('fast'); // Update content and fade in
                    ntis_tags.init();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#tic-wrapper').html(response.data.html).fadeIn('fast'); // On error, still update content and fade in
                console.log(jqXHR, textStatus, errorThrown);
            }
        });
    });
    
    $(document).on('click', '.navigation .page-numbers', function(e) {
        e.preventDefault();
        var pageUrl = $(this).attr('href');
        var pageNumber = getPageNumberFromUrl(pageUrl);

        if (pageNumber) {
            var form = $('#turizmo-istekliai-filter-form');
            paged = pageNumber;
            form.submit();
        }
    });

    function getPageNumberFromUrl(url) {
        var match = url.match(/page\/(\d+)/);
        return match ? match[1] : null;
    }    

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

    
    document.addEventListener('DOMContentLoaded', () => {
        ntis_object_map.init();
        ntis_tags.init();
    });
})(jQuery);