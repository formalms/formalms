import { Calendar } from '@fullcalendar/core';
import itLocale from '@fullcalendar/core/locales/it';
import dayGridPlugin from '@fullcalendar/daygrid';

export const RenderDashBoardCalendar = () => {
  const el = document.querySelector('.js-dashboard-calendar')
		const calendar = new Calendar(el, {
      plugins: [dayGridPlugin],
      locale: itLocale,
      height: 'auto',
      eventSources: [
        {
          events: (fetchInfo, successCallback, failureCallback) => {
            $.ajax({
              type: 'post',
              url: window.dashboardCalendarAjaxUrl,
              data: {
                blockAction: 'getElearningCalendar',
                authentic_request: window.dashboardCalendarAjaxSignature,
                block: 'DashboardBlockCalendarLms'
              },
              success: function (data) {
                const parsedData = JSON.parse(data);
                successCallback(
                  parsedData.response.map((item) => {
                    return {
                      title: item.title,
                      start: item.startDate,
                      type: item.type,
                      status: item.status,
                      description: item.description,
                      hours: item.hours
                    }
                  })
                )
              },
              error: function (e) {
                failureCallback(
                  () => console.log(e)
                )
              }
            });
          },
          color: '#A478EA'
        },
        {
          events: (fetchInfo, successCallback, failureCallback) => {
            $.ajax({
              type: 'post',
              url: window.dashboardCalendarAjaxUrl,
              data: {
                blockAction: 'getClassroomCalendar',
                authentic_request: window.dashboardCalendarAjaxSignature,
                block: 'DashboardBlockCalendarLms'
              },
              success: function (data) {
                const parsedData = JSON.parse(data);

                successCallback(
                  parsedData.response.map((item) => {
                    return {
                      title: item.title,
                      start: item.startDate,
                      type: item.type,
                      status: item.status,
                      description: item.description,
                      hours: item.hours
                    }
                  })
                )
              },
              error: function (e) {
                failureCallback(
                  () => console.log(e)
                )
              }
            });
          },
          color: '#007CC8'
        },
        {
          events: (fetchInfo, successCallback, failureCallback) => {
            $.ajax({
              type: 'post',
              url: window.dashboardCalendarAjaxUrl,
              data: {
                blockAction: 'getReservationCalendar',
                authentic_request: window.dashboardCalendarAjaxSignature,
                block: 'DashboardBlockCalendarLms'
              },
              success: function (data) {
                const parsedData = JSON.parse(data);

                successCallback(
                  parsedData.response.map((item) => {
                    return {
                      title: item.title,
                      start: item.startDate,
                      type: item.type,
                      status: item.status,
                      description: item.description,
                      hours: item.hours
                    }
                  })
                )
              },
              error: function (e) {
                failureCallback(
                  () => console.log(e)
                )
              }
            });
          },
          color: '#007CC8'
        },
        // {
        //   events: [
        //     {
        //       title: 'Corso lorem ipsum',
        //       start: '2019-07-09', //l'orario è opzionale
        //       type: 'elearning',
        //       status: true, //rosso - verde
        //       description: 'Testo testo testo',
        //       hours: '15:30 - 18:30'
        //     }
        //   ],
        //   color: '#A478EA'
        // }
      ],
      eventClick: function() {
        // renderPopup(event);
        // console.log(item)
      },
      eventRender: function(item) {
        // console.log(item);
        if (item.event.extendedProps.status) {
          item.el.classList.add('is-open');
        } else {
          item.el.classList.add('is-closed');
        }
        renderPopup(item);
      }
		})

		calendar.render()
}

const renderPopup = (item) => {
  let el = '';
  const type = item.event.extendedProps.type === 'classroom' ? 'classroom' : 'elearning';
  const desc = item.event.extendedProps.description;
  const hours = item.event.extendedProps.hours;
  const title = item.event.title;
  // console.log(item.event)
  
  el += '<div class="d-popup">';
  el += '<div class="d-popup__item is-' + type + '">';
  el += '<div class="d-popup__title">' + title + '</div>';
  el += '<div class="d-popup__type">' + type + '</div>';
  el += '<div class="d-popup__desc">' + desc + '</div>';
  el += '<div class="d-popup__hours">' + hours + '</div>';
  el += '<div class="d-popup__triangle"></div>';
  el += '</div>';
  el += '</div>';

  $(item.el).append(el);
}