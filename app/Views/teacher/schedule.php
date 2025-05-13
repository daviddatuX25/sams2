<?php $this->extend('layouts/main'); ?>
<?php $this->section('content'); ?>
<div class="row">
    <div class="col-12">
        <h1 class="mb-4">Teaching Schedule</h1>
        <div class="mb-3">
            <a href="<?= site_url('teacher/schedule?view=day') ?>" class="btn btn-primary <?php echo $viewMode === 'day' ? 'active' : ''; ?>">Day</a>
            <a href="<?= site_url('teacher/schedule?view=week') ?>" class="btn btn-primary <?php echo $viewMode === 'week' ? 'active' : ''; ?>">Week</a>
            <a href="<?= site_url('teacher/schedule?view=month') ?>" class="btn btn-primary <?php echo $viewMode === 'month' ? 'active' : ''; ?>">Month</a>
        </div>
        <div class="card">
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: '<?php echo $viewMode === 'day' ? 'timeGridDay' : ($viewMode === 'week' ? 'timeGridWeek' : 'dayGridMonth' );?>',
            events: <?php echo $events; ?>,
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            },
            slotMinTime: '07:00:00',
            slotMaxTime: '20:00:00',
            allDaySlot: false,
            height: 'auto',
            validRange: {
                start: '<?php echo $termStart; ?>',
                end: '<?php echo $termEnd; ?>'
            }
        });
        calendar.render();
    });
</script>
<?php $this->endSection(); ?>