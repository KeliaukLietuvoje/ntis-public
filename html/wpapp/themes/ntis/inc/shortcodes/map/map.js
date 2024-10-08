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
        init: function () {
            const mapContainer = document.getElementById('ntis-map');
            if (!mapContainer || !maplibregl) return;
            const map = new maplibregl.Map({
                container: 'ntis-map',
                style: 'https://basemap.startupgov.lt/vector/styles/bright/style.json',
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

                    

                        // if(map.getZoom() == 10){
                        //     map.getSource('objects').getClusterExpansionZoom(
                        //         clusterId,
                        //         function (err, zoom) {
                        //             if (err) return;
                        
                        //             map.easeTo({
                        //                 center: e.features[0].geometry.coordinates,
                        //                 zoom: zoom
                        //             });
                        //         }
                        //     );
                        // }

                        // try {
                        //     const source = map.getSource('objects');
                        //     // get current zoom
                        //     const zoom = map.getZoom();
                        //     const zoomedIn = zoom + 1;
                            
                        //     map.easeTo({
                        //         center: features[0].geometry.coordinates,
                        //         zoom: zoomedIn
                        //     });
                        // } catch (error) {
                        //     console.error('Error zooming into cluster:', error);
                        // }
                    
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
