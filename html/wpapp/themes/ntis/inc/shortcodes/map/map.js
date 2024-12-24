(function ($, window, document, undefined) {
    'use strict';
    
    String.prototype.sanitizeTitle = function () {
        return this.toLowerCase()
            .replace(/ą/g, 'a')
            .replace(/č/g, 'c')
            .replace(/ę/g, 'e')
            .replace(/ė/g, 'e')
            .replace(/į/g, 'i')
            .replace(/š/g, 's')
            .replace(/ų/g, 'u')
            .replace(/ū/g, 'u')
            .replace(/ž/g, 'z')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '');
    };

    const ntis_map = {
        show_clear_filter_btn: function() {
            $('#ntis-map__filter-clear-filters').addClass('active');
        },
        updateSelectedCount: function(dropdown) {
            let selectedCount = dropdown.find('input[type="checkbox"]:checked').length;
            dropdown.find('.selected-count').text(selectedCount > 0 ? `+${selectedCount}` : '');
            if(selectedCount > 0) {
                $('#ntis-map__filter-clear-filters').addClass('active');
            }
        },
        checkboxChanged:function() {
            var $this = $(this),
                checked = $this.prop("checked"),
                container = $this.closest('li');
            container.find('input[type="checkbox"]').prop({
                checked: checked,
                indeterminate: false
            }).siblings('label')
              .removeClass('custom-checked custom-unchecked custom-indeterminate')
              .addClass(checked ? 'custom-checked' : 'custom-unchecked');
        
            ntis_map.checkSiblings(container, checked);
        },        
        checkSiblings:function($el, checked) {
            var parent = $el.parent().closest('li'), // Find the parent <li> of the current checkbox
                allChecked = true,
                allUnchecked = true;
        
            $el.siblings().each(function() {
                var checkbox = $(this).children('input[type="checkbox"]');
                if (checkbox.prop("checked")) {
                    allUnchecked = false;
                } else {
                    allChecked = false; 
                }
            });
            if (parent.length) {
                var parentCheckbox = parent.children('input[type="checkbox"]');
                parentCheckbox.prop("checked", checked).prop("indeterminate", false)
                    .siblings('label').removeClass('custom-checked custom-unchecked custom-indeterminate')
                    .addClass(checked ? 'custom-checked' : (allChecked ? 'custom-checked' : (allUnchecked ? 'custom-unchecked' : 'custom-indeterminate')));
                
    
                ntis_map.checkSiblings(parent, checked);
            }
        },
        filterCheckboxes: function($t, filterValue) {
            $($t).parents('.dropdown').find('.treeview li').each(function() {
                var $this = $(this);
                var labelText = $this.find('label span').text().toLowerCase();
                
                if (labelText.includes(filterValue)) {
                    $this.show();
                } else {
                    $this.hide();
                }
            });
        },
        initFilters: function (map) {

            $('.dropdown-toggle').on('click', function() {
                let dropdown = $(this).closest('.dropdown');
                dropdown.toggleClass('active');
            });
            $('#ntis-map-filters input[type="checkbox"]').on('change',function () {
                ntis_map.checkboxChanged.call(this);
                let dropdown = $(this).closest('.dropdown');
                ntis_map.updateSelectedCount(dropdown);
                $('#ntis-map-filters').trigger('submit');
            });
            $('#ntis-map__filter_tenant-input').on('keyup', function() {
                var filterValue = $(this).val().toLowerCase(); 
                ntis_map.filterCheckboxes(this,filterValue);

                if (filterValue.length > 0) {
                    $('#ntis-map__filter_tenant-clear-input').show();
                }else {
                    $('#ntis-map__filter_tenant-clear-input').hide();
                }
            });

            $('#ntis-map__filter-input').on('keyup', function() {
                var filterValue = $(this).val().toLowerCase(); 
                ntis_map.filterCheckboxes(this,filterValue);

                if (filterValue.length > 0) {
                    $('#ntis-map__filter-clear-input').show();
                }else {
                    $('#ntis-map__filter-clear-input').hide();
                }
            });
            $('#ntis-map__filter_tenant-clear-input').on('click', function() {
                $('#ntis-map__filter_tenant-input').val('');
                $(this).parents('.dropdown').find('input[type="checkbox"]').prop('checked', false);
                ntis_map.filterCheckboxes(this,'');
                $(this).removeClass('active');
                let dropdown = $(this).parents('.dropdown');
                ntis_map.updateSelectedCount(dropdown);
                $('#ntis-map-filters').trigger('submit');
            });
            $('#ntis-map__filter-clear-input').on('click', function() {
                $('#ntis-map__filter-input').val('');
                $(this).parents('.dropdown').find('input[type="checkbox"]').prop('checked', false);
                ntis_map.filterCheckboxes(this,'');
                $(this).removeClass('active');
                let dropdown = $(this).parents('.dropdown');
                ntis_map.updateSelectedCount(dropdown);
                $('#ntis-map-filters').trigger('submit');
            });
            $('#ntis-map__filter-clear-filters').on('click', function() {
                $('#ntis-map-filters input[type="checkbox"]').prop('checked', false);
                $('#filter_category_form label').removeClass('custom-checked custom-unchecked custom-indeterminate');
                $('.treeview li').show();
                $('#ntis-map__filter-input,#filter_title,#ntis-map__filter_tenant-input').val('');
                $('#ntis-map-filters .dropdown').each(function() {
                    let dropdown = $(this).closest('.dropdown');
                    ntis_map.updateSelectedCount(dropdown);
                });
                $(this).removeClass('active');
                $('#ntis-map-filters').trigger('submit');
            });

            let typingTimer;
            const debounceTime = 500; 
            $('#filter_title').on('blur input paste', function() {
                clearTimeout(typingTimer); 
                typingTimer = setTimeout(function() {
                    $('#ntis-map__filter-clear-filters').addClass('active');
                    $('#ntis-map-filters').trigger('submit');
                }, debounceTime);
            });

            $(window).on('click', function(e) {
                $('.dropdown').each(function() {
                    if (!$(this).is(e.target) && $(this).has(e.target).length === 0) {
                        $(this).removeClass('active');
                    }
                });
            });

            $('#ntis-map-filters').on('submit', async function(e) {
                e.preventDefault();
            
                // Prepare the query object
                const queryObject = {
                    isPaid: '',
                    nameLt: { $ilike: [] },
                    nameEn: { $ilike: [] },
                    categories: { id: { $in: [] } },
                    tenant: { id: { $in: [] } },
                    additionalInfos: { id: { $in: [] } },
                };
            
                // Check the title input
                const title = $('#filter_title').val();
                if (title && title.length > 0) {
                    if (ntis_map_config.lang === 'lt') {
                        queryObject.nameLt.$ilike = '%' + title + '%';
                    } else {
                        queryObject.nameEn.$ilike = '%' + title + '%';
                    }
                }
                if (!queryObject.nameLt.$ilike.length) {
                    delete queryObject.nameLt;
                }
                if (!queryObject.nameEn.$ilike.length) {
                    delete queryObject.nameEn;
                }
            
                // Process category filters
                $('#filter_category_form input[type="checkbox"]').each(function() {
                    if ($(this).prop('checked')) {
                        queryObject.categories.id.$in.push(parseInt($(this).val()));
                    }
                });
                if (queryObject.categories.id.$in.length === 0) {
                    delete queryObject.categories;
                }

                // Process tenants filters
                $('#filter_tenant_form input[type="checkbox"]').each(function() {
                    if ($(this).prop('checked')) {
                        queryObject.tenant.id.$in.push(parseInt($(this).val()));
                    }
                });
                if (queryObject.tenant.id.$in.length === 0) {
                    delete queryObject.tenant;
                }
            
                // Process price filters
                $('#filter_price_form input[type="checkbox"]').each(function() {
                    if ($(this).prop('checked')) {
                        queryObject.isPaid = $(this).val() === 'paid' ? 'true' : 'false';
                    }
                });
                if (queryObject.isPaid.length === 0 || $('#filter_price_form input[type="checkbox"]:checked').length === 2) {
                    delete queryObject.isPaid;
                }
            
                // Process additional info filters
                $('#filter_additionalinfo_form input[type="checkbox"]').each(function() {
                    if ($(this).prop('checked')) {
                        queryObject.additionalInfos.id.$in.push(parseInt($(this).val()));
                    }
                });
                if (queryObject.additionalInfos.id.$in.length === 0) {
                    delete queryObject.additionalInfos;
                }
            
                if(Object.keys(queryObject).length === 0) {
                    $('#ntis-map__filter-clear-filters').removeClass('active');
                }
                // Create the query string for the tiles URL
                const query = encodeURIComponent(JSON.stringify(queryObject));
                const tiles_url = `${ntis_map_config.api.url}/tiles/objects/{z}/{x}/{y}/?query=${query}`;
            
                // Remove layers that use the source before removing the source
                const layersToRemove = ['cluster-circle', 'point', 'cluster'];
            
                // Check and remove layers
                for (const layer of layersToRemove) {
                    if (map.getLayer(layer)) {
                        map.removeLayer(layer);
                    }
                }
            
                // Now remove the source
                if (map.getSource('objects')) {
                    map.removeSource('objects');
                }
            
                // Add the new source with the updated tiles URL
                map.addSource('objects', {
                    type: 'vector',
                    tiles: [tiles_url],
                });
            
                // Re-add the layers with the updated source
                map.addLayer({
                    id: 'cluster-circle',
                    type: 'circle',
                    filter: ['all', ['has', 'cluster_id']],
                    paint: {
                        'circle-color': '#003D2B',
                        'circle-opacity': 0.3,
                        'circle-radius': 20,
                    },
                    source: 'objects',
                    'source-layer': 'objects',
                });
            
                map.addLayer({
                    id: 'point',
                    type: 'symbol',
                    source: 'objects',
                    filter: ['all', ['!has', 'cluster_id']],
                    layout: {
                        'icon-image': 'ico',
                        'icon-size': 1
                    },
                    'source-layer': 'objects',
                }, 'cluster-circle');
            
                map.addLayer({
                    id: 'cluster',
                    type: 'symbol',
                    source: 'objects',
                    'source-layer': 'objects',
                    filter: ['all', ['has', 'cluster_id']],
                    layout: {
                        'text-field': "{point_count}",
                        'text-font': ['Noto Sans Regular'],
                        'text-size': 16,
                    },
                    paint: {
                        'text-color': '#000000'
                    },
                });
            });
            

        },
        init: function () {
            const mapContainer = document.getElementById('ntis-map');
            if (!mapContainer || !maplibregl) return;
            const map = new maplibregl.Map({
                container: 'ntis-map',
                style: 'https://basemap.biip.lt/styles/bright/style.json',
                center: ntis_map_config.coordinates,
                zoom: ntis_map_config.zoom,
                attributionControl: false
            });

            map.addControl(new maplibregl.AttributionControl({
                compact: true,
                customAttribution: '© <a href="https://ntis.lt/">Turizmo išteklių posistemė</a>'
            }));

            map.on('load', function() {
                const attributionDetails = document.querySelector('.maplibregl-compact');
                if (attributionDetails) {
                    attributionDetails.removeAttribute('open'); 
                    attributionDetails.classList.remove('maplibregl-compact-show'); 
                }
            });

            if(ntis_map_config.filter_enabled) {
                ntis_map.initFilters(map);
            }

            if (ntis_map_config.add_layer === 'true') {
              
                map.on('load', async () => {
                    const image = await map.loadImage(ntis_map_config.pin.url);
     
                    //map.addImage('cat', image.data);
                    map.addImage('ico', image.data, {
                        width: ntis_map_config.pin.size[0],
                        height: ntis_map_config.pin.size[1]
                    });

                    // Add source and layers
                    map.addSource('objects', {
                        type: 'vector',
                        tiles: [ntis_map_config.api.url + '/tiles/objects/{z}/{x}/{y}'],
                    });

                    map.addLayer({
                        id: 'cluster-circle',
                        type: 'circle',
                        filter: ['all', ['has', 'cluster_id']],
                        paint: {
                            'circle-color': '#003D2B',
                            'circle-opacity': 0.3,
                            'circle-radius': 20,
                        },
                        source: 'objects',
                        'source-layer': 'objects',
                    });

                    map.addLayer({
                        id: 'point',
                        type: 'symbol',
                        source: 'objects',
                        filter: ['all', ['!has', 'cluster_id']],
                        layout: {
                            'icon-image': 'ico',
                            'icon-size': 1
                        },
                        'source-layer': 'objects',
                    }, 'cluster-circle');

                    map.addLayer({
                        id: 'cluster',
                        type: 'symbol',
                        source: 'objects',
                        'source-layer': 'objects',
                        filter: ['all', ['has', 'cluster_id']],
                        layout: {
                            'text-field': "{point_count}",
                            'text-font': ['Noto Sans Regular'],
                            'text-size': 16,
                        },
                        paint: {
                            'text-color': '#000000'
                        },
                    });
                    map.on('click', 'cluster', async (e) => {
                        var features = map.queryRenderedFeatures(e.point, {
                            layers: ['cluster']
                        });

                        var clusterId = features[0].properties.cluster_id;
                    
                        // Get the cluster's center coordinates
                        const centerCoords = features[0].geometry.coordinates;
                    
                        // Create a popup reference
                        let popup = new maplibregl.Popup()
                            .setLngLat(centerCoords)  // Set the cluster's center coordinates
                            .addTo(map);
                    
                        // Fetch the first page of data
                        let currentPage = 1;
                        let totalPages = 1; // Will be updated dynamically after fetching
                        let data = {};
                    
                        async function fetchData(page = 1) {
                            await fetch(ntis_map_config.api.url + `/tiles/objects/cluster/${clusterId}/items?page=${page}&pageSize=1`)
                                .then(response => response.json())
                                .then(result => {
                                    data = result;
                                    totalPages = result.totalPages;
                                    updatePopup(result.rows[0]);
                                });
                        }
                    
                        function updatePopup(item) {
                            const arrow_right = '<svg class="place__detail__icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>';
                        
                            const btn_left = '<svg class="place__detail__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-left"><path d="m15 18-6-6 6-6"/></svg>';
                        
                            const btn_right = '<svg class="place__detail__icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right"><path d="m9 18 6-6-6-6"/></svg>';
                        
                            const sanitizedTitle = ntis_map_config.lang == 'lt' ? item.nameLt.sanitizeTitle() : item.nameEn.sanitizeTitle();
                            const title = ntis_map_config.lang == 'lt' ? item.nameLt : item.nameEn;
                        
                            let popupContent = `
                                <div id="popup-content" class="ntis-popup">`;
                                if (item.photos && item.photos.length > 0) {
                                    popupContent += `<img src="${item.photos[0].url}" alt="${title}" />`;
                                }
                            popupContent += `
                                    <h3>${title}</h3>
                                    <a target="_blank" class="place__url" href="${ntis_map_config.more_url}/${sanitizedTitle}/id:${item.id}/">${ntis_map_config.i18n.more}${arrow_right}</a>
                                </div>
                                <div id="popup-navigation">
                                <span>${ntis_map_config.i18n.object}</span>
                                <div>
                                    <button id="prev" ${currentPage === 1 ? 'disabled' : ''}>${btn_left}</button>
                                    <input id="pageInput" type="number" min="1" max="${totalPages}" value="${currentPage}" />
                                    <span> iš ${totalPages}</span>
                                    <button id="next" ${currentPage === totalPages ? 'disabled' : ''}>${btn_right}</button>
                                </div>
                                </div>
                            `;
                            popupContent += '</div>';
                        
                            // Update the popup content dynamically
                            popup.setHTML(popupContent);
                        
                            // Add event listeners for navigation
                            document.getElementById('prev').addEventListener('click', () => {
                                if (currentPage > 1) {
                                    currentPage--;
                                    fetchData(currentPage);
                                }
                            });
                        
                            document.getElementById('next').addEventListener('click', () => {
                                if (currentPage < totalPages) {
                                    currentPage++;
                                    fetchData(currentPage);
                                }
                            });
                        
                            document.getElementById('pageInput').addEventListener('change', (e) => {
                                const newPage = parseInt(e.target.value);
                                if (newPage >= 1 && newPage <= totalPages) {
                                    currentPage = newPage;
                                    fetchData(currentPage);
                                } else {
                                    e.target.value = currentPage; // Reset to current page if invalid
                                }
                            });
                        }
                        // Fetch and display the first page of data
                        fetchData(currentPage);

                    });
                    
                    map.on('click', 'point', (e) => {
                        const coordinates = e.features[0].geometry.coordinates.slice();
                        const featureId = e.features[0].properties.id;

                        // Ensure the popup opens at the correct coordinates
                        while (Math.abs(e.lngLat.lng - coordinates[0]) > 180) {
                            coordinates[0] += e.lngLat.lng > coordinates[0] ? 360 : -360;
                        }

                        // Fetch additional data for the feature
                        fetch(`${ntis_map_config.api.url}/tiles/objects/?query[id]=${featureId}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data && data.rows && data.rows.length > 0) {
                                    const featureData = data.rows[0];
                                    
                                    // Sanitize title for the URL
                                    const sanitizedTitle = ntis_map_config.lang == 'lt' ? featureData.nameLt.sanitizeTitle() : featureData.nameEn.sanitizeTitle();
                                    const title = ntis_map_config.lang == 'lt' ? featureData.nameLt : featureData.nameEn;

                                    let popupContent = '<div class="ntis-popup">';

                                    // Add the first image if available
                                    if (featureData.photos && featureData.photos.length > 0) {
                                        popupContent += `<img src="${featureData.photos[0].url}" alt="${featureData.nameLt}" />`;
                                    }
                                    popupContent += `<h3>${title}</h3>`;
                                    const arrow_right = '<svg class="place__detail__icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>';

                                    // Add the link
                                    popupContent += `<a target="_blank" class="place__url" href="${ntis_map_config.more_url}/${sanitizedTitle}/id:${featureData.id}/">${ntis_map_config.i18n.more}${arrow_right}</a>`;

                                    popupContent += '</div>';
                                    // Create and display the popup
                                    new maplibregl.Popup({ offset: 25 })
                                        .setLngLat(coordinates)
                                        .setHTML(popupContent)
                                        .addTo(map);
                                } else {
                                    console.error('No data found for the feature.');
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching feature data:', error);
                            });
                    });

                    map.on('mouseenter', 'point', () => {
                        map.getCanvas().style.cursor = 'pointer';
                    });

                    map.on('mouseleave', 'point', () => {
                        map.getCanvas().style.cursor = '';
                    });
                });
                
            } else {
                const el = document.createElement('div');
                el.className = 'marker';
                el.style.backgroundImage = `url(${ntis_map_config.pin.url})`;
                el.style.width = `${ntis_map_config.pin.size[0]}px`;
                el.style.height = `${ntis_map_config.pin.size[1]}px`;

                // Create a popup instance
                const popup = new maplibregl.Popup({ offset: 25 }).setText('');

                new maplibregl.Marker({ element: el })
                    .setLngLat(ntis_map_config.coordinates)
                    .setPopup(popup)
                    .addTo(map);
            }
                  
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        ntis_map.init();
    });

}(jQuery, window, document));
