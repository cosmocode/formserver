// Inject flatpickr
flatpickr('[data-calender-type="date"]', {'dateFormat' : 'd.m.Y', 'allowInput' : true});
flatpickr('[data-calender-type="time"]', {'noCalendar' : true, 'enableTime' : true, 'time_24hr' : true, 'allowInput' : true});
flatpickr('[data-calender-type="datetime"]', {'enableTime' : true, 'time_24hr' : true, 'dateFormat' : 'd.m.Y H:i', 'allowInput' : true});
