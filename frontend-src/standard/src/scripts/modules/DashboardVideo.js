// const $ = require('jquery');

export const DashboardVideo = () => {
  const elementsArray = document.querySelectorAll('.js-dashboard-video');

  for (var i = 0; i < elementsArray.length; i++) {
    elementsArray[i].addEventListener('click', onDashboardVideoClick);
  }
}

const onDashboardVideoClick = (event) => {
  switch (event.target.dataset.videoType) {
    case 'yt':
      console.log('video youtube ---> ' + event.target.dataset.videoUrl);
      break;

    case 'vimeo':
      console.log('video vimeo ---> ' + event.target.dataset.videoUrl);
      break;
  }
}

// const renderPopup = (item) => {
//   let el = '';
//   const type = item.event.extendedProps.type === 'classroom' ? 'classroom' : 'elearning';
//   const desc = item.event.extendedProps.description;
//   const hours = item.event.extendedProps.hours;
//   const title = item.event.title;

//   el += '<div class="d-popup">';
//   el += '<div class="d-popup__item is-' + type + '">';
//   el += '<div class="d-popup__title">' + title + '</div>';
//   el += '<div class="d-popup__type">' + type + '</div>';
//   el += '<div class="d-popup__desc">' + desc + '</div>';
//   el += '<div class="d-popup__hours">' + hours + '</div>';
//   el += '<div class="d-popup__triangle"></div>';
//   el += '</div>';
//   el += '</div>';

//   $(item.el).append(el);
// }
