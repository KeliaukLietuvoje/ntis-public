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
        reinit() {
            // while window resizing hide tags and show when resize is done
            document.querySelectorAll('.tags-wrapper').forEach(wrapper => {
                const tags = Array.from(wrapper.querySelectorAll('.tag'));
                const moreButton = wrapper.querySelector('.more-button');
                
                // Remove all inline styles from tags on init
                tags.forEach(tag => tag.removeAttribute('style'));
                
                if (moreButton) {
                    moreButton.addEventListener('click', () => this.showAllTags(wrapper, tags, moreButton));
                }
                
                const adjustVisibility = () => this.adjustTagVisibility(wrapper, tags, moreButton);
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
            container = $this.closest('li'); // Find the closest <li> that contains this checkbox
    
        // Check or uncheck all nested checkboxes based on the parent checkbox's state
        container.find('input[type="checkbox"]').prop({
            checked: checked,
            indeterminate: false
        }).siblings('label')
          .removeClass('custom-checked custom-unchecked custom-indeterminate')
          .addClass(checked ? 'custom-checked' : 'custom-unchecked');
    
        // Check siblings to update their state
        checkSiblings(container, checked);
    }
    
    function checkSiblings($el, checked) {
        var parent = $el.parent().closest('li'), // Find the parent <li> of the current checkbox
            allChecked = true,
            allUnchecked = true;
    
        $el.siblings().each(function() {
            var checkbox = $(this).children('input[type="checkbox"]');
            if (checkbox.prop("checked")) {
                allUnchecked = false; // At least one checkbox is checked
            } else {
                allChecked = false; // At least one checkbox is unchecked
            }
        });
    
        // Update the parent checkbox based on the state of its children
        if (parent.length) {
            var parentCheckbox = parent.children('input[type="checkbox"]');
            parentCheckbox.prop("checked", checked).prop("indeterminate", false)
                .siblings('label').removeClass('custom-checked custom-unchecked custom-indeterminate')
                .addClass(checked ? 'custom-checked' : (allChecked ? 'custom-checked' : (allUnchecked ? 'custom-unchecked' : 'custom-indeterminate')));
            
            // Recursively check the parent's parent
            checkSiblings(parent, checked);
        }
    }


    $(window).on('resize', function() {
        ntis_tags.reinit();
        if($(window).width() > 768) {
            $('.tic-place__filters').removeClass('closed');
            $('.tic-place__content').removeClass('full-width');
            $('.btn__filters--toggle').text(objVars.i18n.hide_filters);
        } else {
            $('.tic-place__filters').addClass('closed');
            $('.tic-place__content').addClass('full-width');
            $('.btn__filters--toggle').text(objVars.i18n.show_filters);
        }
    });
    $('.btn__filters--toggle').on('click', function() {
        $('.tic-place__filters').toggleClass('closed');
        $('.tic-place__content').toggleClass('full-width');
        
        if($('.tic-place__filters').hasClass('closed')) {
            $('.btn__filters--toggle').text(objVars.i18n.show_filters);
        } else {
            $('.btn__filters--toggle').text(objVars.i18n.hide_filters);
        }
    });

    document.querySelectorAll('.filter-wrapper').forEach(wrapper => {
        const title = wrapper.querySelector('.filter-title');
        const content = wrapper.querySelector('.filter-content');
        const toggleButton = wrapper.querySelector('.filter-toggle');
        const iconSpan = toggleButton.querySelector('span');

        title.addEventListener('click', function() {
            const isExpanded = toggleButton.getAttribute('aria-expanded') === 'true';
            content.style.display = isExpanded ? 'none' : 'block';
            toggleButton.setAttribute('aria-expanded', !isExpanded);
            if (isExpanded) {
                iconSpan.classList.remove('ico-expanded');
                iconSpan.classList.add('ico-collapsed');
            } else {
                iconSpan.classList.remove('ico-collapsed');
                iconSpan.classList.add('ico-expanded');
            }
        });

        const showMoreBtn = wrapper.querySelector('.show-more');
        const moreOptions = wrapper.querySelectorAll('.more-options');

        if(!showMoreBtn) return;
        showMoreBtn.addEventListener('click', function() {
            moreOptions.forEach(option => {
                if (option.style.display === 'grid') {
                    option.style.display = 'none';
                    showMoreBtn.textContent = objVars.i18n.show_more;
                } else {
                    option.style.display = 'grid';
                    showMoreBtn.textContent = objVars.i18n.show_less;
                }
            });
        });
    });



    $('#turizmo-istekliai-filter-form .filter-content input[type=checkbox]').change(function () {
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
                $('#tic-wrapper').fadeOut('fast');
            },
            success: function(response) {
                if (response.success) {
                    $('#tic-wrapper').html(response.data.html).fadeIn('fast');
                    $('.tic-total').text(response.data.total);
                    ntis_tags.init();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#tic-wrapper').html(response.data.html).fadeIn('fast'); 
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