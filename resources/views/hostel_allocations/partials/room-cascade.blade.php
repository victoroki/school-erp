{{--
    Keeps the room <select> in step with the chosen hostel.

    Plain ES5/DOM on purpose: the ERP attaches jQuery from a Vite module that
    only runs after the document has finished parsing, so inline jQuery at this
    point in the page is unreliable. This works with or without select2 and
    refreshes the select2 UI when it is available.
--}}
<script>
    (function () {
        var hostel = document.getElementById('hostel_select');
        var room = document.getElementById('room_select');
        if (!hostel || !room) { return; }

        function apply() {
            var chosen = hostel.value;
            var currentIsInvalid = false;

            Array.prototype.forEach.call(room.options, function (option) {
                if (!option.value) { return; }

                var matches = !chosen || option.getAttribute('data-hostel') === chosen;
                option.hidden = !matches;
                option.disabled = !matches;

                if (!matches && option.selected) { currentIsInvalid = true; }
            });

            if (currentIsInvalid) { room.value = ''; }

            // Re-render the select2 widget so hidden options disappear from it too.
            if (window.jQuery && window.jQuery.fn.select2) {
                var $room = window.jQuery(room);
                if ($room.data('select2')) { $room.trigger('change.select2'); }
            }
        }

        hostel.addEventListener('change', apply);
        apply();
    })();
</script>
