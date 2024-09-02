(function ($, window, document, undefined) {
    'use strict';
    
    const ntis_map = {
        init: function () {
            const mapContainer = document.getElementById('ntis-map');
            if (!mapContainer || !maplibregl) return;
            const map = new maplibregl.Map({
                container: 'ntis-map',
                style: 'https://basemap.startupgov.lt/vector/styles/bright/style.json',
                center: ntis_map_config.coordinates,
                zoom: ntis_map_config.zoom
            });
            console.log(ntis_map_config);
            if (ntis_map_config.add_layer === 'true') {
                map.on('load', () => {
                    map.addSource('registras', {
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
                        source: 'registras',
                        'source-layer': 'objects',
                    });

                    map.addLayer({
                        id: 'point',
                        type: 'circle',
                        source: 'registras',
                        filter: ['all', ['!has', 'cluster_id']],
                        paint: {
                            'circle-color': '#003D2B',
                            'circle-opacity': 1,
                            'circle-radius': 5,
                        },
                        'source-layer': 'sportsBases',
                    });

                    map.addLayer({
                        id: 'cluster',
                        type: 'symbol',
                        source: 'registras',
                        'source-layer': 'sportsBases',
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
            }else{
                const el = document.createElement('div');
                el.className = 'marker';
                el.style.backgroundImage = `url(${ntis_map_config.pin.url})`;
                el.style.width = `${ntis_map_config.pin.size[0]}px`;
                el.style.height = `${ntis_map_config.pin.size[1]}px`;

                new maplibregl.Marker({element: el})
                    .setLngLat(ntis_map_config.coordinates)
                    .addTo(map);
            }
        }
    }
    document.addEventListener('DOMContentLoaded', () => {
        ntis_map.init();
    });

}(jQuery, window, document));