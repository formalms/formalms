import { Calendar } from '@fullcalendar/core';
import itLocale from '@fullcalendar/core/locales/it';
import dayGridPlugin from '@fullcalendar/daygrid';

export default class DashboardCalendar {
	constructor() {
		this.el = document.querySelector('.js-dashboard-calendar')
		this.calendar = new Calendar(this.el, {
      plugins: [dayGridPlugin],
      locale: itLocale,
      eventSources: [
        {
          url: window.dashboardCalendarAjaxUrl,
          headers: {
            'signature': window.dashboardCalendarAjaxSignature
          },
          method: 'POST',
          extraParams: {
            blockAction: 'getElearningCalendar',
          },
          failure: function(err) {
            console.log(err);
          },
          color: 'purple',
          textColor: 'white'
        }
      ]
			// events: [
				// {
				// 	title  : 'event1',
				// 	start  : '2019-06-29',
				// 	type: 'classroom'
				// },
				// {
				// 	title: 'new event',
				// 	start: '2019-09-28',
				// 	type: 'yeah'
				// },
				// {
				// 	title: 'Corso lorem ipsum',
				// 	start: '2019-06-29T12:30:00', //l'orario è opzionale
				// 	end: '2019-06-29T16:30:00', // se il corso è di un solo giorno l'end è opzionale
				// 	type: 'classroom etcetc',
				// 	status: '', //rosso - verde
				// 	description: '',
				//  hours: '15:30 - 18:30'
				// }
			// ],
		})

		this.calendar.render()
	}
}