import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import initialLocaleCode from '@fullcalendar/core/locales/it';

export const DashBoardCalendar = () => {
  const els = document.querySelectorAll('.js-dashboard-calendar');
  const colors = {
      'getElearningCalendar' : '#A478EA',
      'getClassroomCalendar' : '#007CC8',
      'getReservationCalendar' : '#007CC8'
    };
  if (els.length) {
      
    for (let i = 0; i < els.length; i++) {
      const actions = els[i].getAttribute('data-action');
      const actionsArray = actions.split(',');

      const calendar = new Calendar(els[i], {
        plugins: [dayGridPlugin],
        locale: initialLocaleCode,
        height: 'auto',
        eventClick: function(item) {
          const id = item.event.id;          
          const url = `index.php?modname=course&op=aula&idCourse=${id}`;

          window.location = url;
        },
        eventDidMount: function(item) {
          if (item.event.extendedProps.status) {
            item.el.classList.add('is-open');
          } else {
            item.el.classList.add('is-closed');
          }

          appendPopup(item);
          return item;
        }
      });


      if (initialLocaleCode !== window.frontend.config.lang.currentLangCode) {
        calendar.setOption('locale', window.frontend.config.lang.currentLangCode);
      }

      for (i = 0; i < actionsArray.length; i++) {
        let dataBlock = actionsArray[i];
        let event = {
          events: (fetchInfo, successCallback, failureCallback) => {
            var dateSt = new Date(fetchInfo.startStr);
            var startDate = dateSt.getFullYear()+'-' + ('0' + (dateSt.getMonth()+1)).slice(-2) + '-' + ('0'+dateSt.getDate()).slice(-2);
            var dateEnd = new Date(fetchInfo.endStr);
            var endDate = dateEnd.getFullYear() +'-' + ('0' + (dateEnd.getMonth()+1)).slice(-2) + '-' + ('0'+dateEnd.getDate()).slice(-2);


            $.ajax({
              type: 'post',
              url: window.dashboardCalendarAjaxUrl,
              data: {
                blockAction: dataBlock,
                authentic_request: window.dashboardCalendarAjaxSignature,
                block: 'DashboardBlockCalendarLms',
                dashboardLayoutId : window.dashboardLayoutId,
                startDate: startDate,
                endDate: endDate
              },
              success: function (data) {
                const parsedData = JSON.parse(data);
                successCallback(
                    parsedData.response.map((item) => {
                      return {
                        id: item.id,
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
          color: colors[actionsArray[i]]
        };
   
        calendar.addEventSource(event);
      }

      calendar.render();
    }
  }

}

const appendPopup = (item) => {
  let el = '';
  const type = item.event.extendedProps.type === 'classroom' ? 'classroom' : 'elearning';
  const desc = item.event.extendedProps.description;
  const hours = item.event.extendedProps.hours;
  const title = item.event.title;

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