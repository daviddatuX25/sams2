<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div class="container mt-4">
    <h1>Schedule</h1>
    <div class="mb-4">
        <div class="mb-4">
        <button class="btn <?= $viewMode === 'day' ? 'btn-primary' : 'btn-outline-primary' ?>" data-view="timeGridDay">Day</button>
        <button class="btn <?= $viewMode === 'week' ? 'btn-primary' : 'btn-outline-primary' ?>" data-view="timeGridWeek">Week</button>
        <button class="btn <?= $viewMode === 'month' ? 'btn-primary' : 'btn-outline-primary' ?>" data-view="dayGridMonth">Month</button>
</div>

    </div>
    <div id="calendar"></div>
</div>
<script>
$(document).ready(function () {
    const calendarEl = document.getElementById('calendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: '<?= $viewMode === 'day' ? 'timeGridDay' : ($viewMode === 'month' ? 'dayGridMonth' : 'timeGridWeek') ?>',
        events: <?= $events ?>,
        validRange: {
            start: '<?= $termStart ?>',
            end: '<?= $termEnd ?>'
        },
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: ''
        },
        eventClick: function(info) {
            if (info.event.url) {
                info.jsEvent.preventDefault();
                window.location.href = info.event.url;
            }
        },
        eventBackgroundColor: '#3A98B9',
        eventBorderColor: '#3A98B9',
        eventTextColor: '#FFFFFF',
        themeSystem: 'bootstrap5',
        height: 'auto',
        slotMinTime: '07:00:00',
        slotMaxTime: '22:00:00'
    });

    calendar.render();

    // jQuery-based view switch handler
    $('button[data-view]').on('click', function () {
        const newView = $(this).data('view');
        calendar.changeView(newView);
        
        // Optional: Update button styles
        $('button[data-view]').removeClass('btn-primary').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('btn-primary');
    });
});
</script>

<style>
    .fc .fc-button-primary {
        background-color: #3A98B9;
        border-color: #3A98B9;
    }
    .fc .fc-button-primary:hover {
        background-color: #2a7a94;
        border-color: #2a7a94;
    }
    .fc .fc-button-primary:not(:disabled).fc-button-active,
    .fc .fc-button-primary:not(:disabled):active {
        background-color: #2a7a94;
        border-color: #2a7a94;
    }
    :root {
        --fc-today-bg-color: rgba(58, 152, 185, 0.1);
    }
</style>
<?php $this->endSection(); ?>