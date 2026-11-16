{{--
    Route -> Pickup Stop / Drop Stop cascade for student transport assignments.

    Both the create and edit screens need this, and they drifted apart before: edit
    refreshed the Select2 after the AJAX call and create did not, so on create the
    stop dropdowns opened showing the old "Select Stop" placeholder over an empty
    search index. Kept in one place so that cannot happen again.

    Select2 keeps its own copy of the option list, so replacing <option> elements
    underneath it changes nothing on screen until the widget is told to re-read
    them. Every path that rewrites the options therefore ends in refresh().

    Expects #route_select, #pickup_stop_select, #drop_stop_select and the two
    help texts rendered by the calling form.
--}}
@push('page_scripts')
<script>
    (function () {
        var STOPS_URL = @json(route('api.routes.stops', ['__ROUTE_ID__']));

        var $route, $pickup, $drop, $pickupHelp, $dropHelp;
        var stops = [];

        function formatTime(value) {
            if (!value) {
                return null;
            }
            // The API hands back a MySQL TIME string, 07:30:00. Show 07:30, matching
            // the label stopOptionLabel() builds on the server.
            var match = String(value).match(/^(\d{1,2}):(\d{2})/);
            return match ? match[1].padStart(2, '0') + ':' + match[2] : String(value);
        }

        function stopLabel(stop) {
            // stop_time is nullable; don't render a stray empty bracket pair.
            var time = formatTime(stop.stop_time);
            return time ? stop.stop_name + '  (' + time + ')' : stop.stop_name;
        }

        function refresh($select) {
            if ($select.data('select2')) {
                $select.trigger('change');
            }
        }

        function say($help, message, isError) {
            $help
                .text(message)
                .toggleClass('text-danger', !!isError)
                .toggleClass('text-muted', !isError);
        }

        function fillStops() {
            var pickupId = $pickup.val();
            var dropId = $drop.val();

            $pickup.empty().append(new Option('Select a pickup stop', ''));
            $drop.empty().append(new Option('Select a drop stop', ''));

            stops.forEach(function (stop) {
                var id = String(stop.stop_id);

                $pickup.append(new Option(
                    stopLabel(stop), stop.stop_id, false, id === String(pickupId)
                ));

                // Nobody is dropped off at the stop they were picked up from.
                if (id !== String(pickupId)) {
                    $drop.append(new Option(
                        stopLabel(stop), stop.stop_id, false, id === String(dropId)
                    ));
                }
            });

            refresh($pickup);
            refresh($drop);

            if (stops.length === 0) {
                say($pickupHelp, 'This route has no stops yet.', true);
                say($dropHelp, 'This route has no stops yet.', true);
            } else {
                say($pickupHelp, stops.length + (stops.length === 1 ? ' stop' : ' stops') + ' on this route.', false);
                say($dropHelp, stops.length > 1
                    ? stops.length + ' stops on this route.'
                    : 'Add a second stop to choose a drop point.', false);
            }
        }

        function clearStops(message) {
            stops = [];
            $pickup.empty().prop('disabled', true);
            $drop.empty().prop('disabled', true);
            say($pickupHelp, message, false);
            say($dropHelp, message, false);
        }

        function loadStops(routeId) {
            if (!routeId) {
                clearStops('Choose a route first.');
                return;
            }

            $pickup.prop('disabled', true).prop('aria-busy', true);
            $drop.prop('disabled', true).prop('aria-busy', true);
            say($pickupHelp, 'Loading stops…', false);
            say($dropHelp, 'Loading stops…', false);

            window.jQuery.getJSON(STOPS_URL.replace('__ROUTE_ID__', routeId))
                .done(function (data) {
                    stops = data || [];
                    fillStops();

                    if (stops.length > 0) {
                        $pickup.prop('disabled', false).prop('aria-busy', false);
                        $drop.prop('disabled', false).prop('aria-busy', false);
                    }
                })
                .fail(function () {
                    clearStops('Stops could not be loaded. Try again.');
                });
        }

        function wire() {
            $route = window.jQuery('#route_select');
            $pickup = window.jQuery('#pickup_stop_select');
            $drop = window.jQuery('#drop_stop_select');
            $pickupHelp = window.jQuery('#pickup_stop_help');
            $dropHelp = window.jQuery('#drop_stop_help');

            if (!$route.length || $route.data('stopCascadeWired')) {
                return;
            }
            $route.data('stopCascadeWired', true);

            $route.on('change', function () {
                loadStops($route.val());
            });

            // Choosing a pickup invalidates a drop that matched it.
            $pickup.on('change', function () {
                fillStops();
            });

            // Populate straight away when a route is already chosen: on edit, and
            // when the form is redisplayed after a validation failure.
            loadStops($route.val());
        }

        window.jQuery(document).on('select2:ready', wire);

        // The layout fires select2:ready on its own DOMContentLoaded, which may
        // land either side of this script running.
        if (window.jQuery('#route_select').data('select2')) {
            wire();
        }
    })();
</script>
@endpush
