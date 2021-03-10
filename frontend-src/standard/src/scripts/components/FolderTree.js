import { contextmenu } from 'easycontext';
import Config from '../config/config';
const axios = require('axios');
import Sortable from 'sortablejs/modular/sortable.complete.esm.js';
import Tree from '../twig/tree.html.twig';
import Content from '../twig/content.html.twig';

class FolderTree {

  constructor() {
      const btn = document.querySelector('.js-ft-rename-el');
      const inputRename = document.querySelector('.folderTree__rename__input');
      this.dragged;

      if (document.querySelectorAll('.folderTree__link').length) {
        contextMenu();
      }

      if (btn) {
        btn.addEventListener('click', () => {
          this.renameEl();
        });
      }

      if (inputRename) {
        inputRename.addEventListener('keyup', (e) => {
          if (e.keyCode === 13) {
            this.renameEl();
            e.preventDefault();
          }
        });
      }

      document.addEventListener('contextmenu', (event) => {
        if (event.target.classList.contains('folderTree__rename__input') || event.target.classList.contains('folderTree__rename__btn') ) {
          document.querySelector('.context-menu').classList.remove('menu-visible');
        }
      });

      document.addEventListener('click', this.clickOnFolder);
      initSortable();
      initDragDrop();
  }

  clickOnFolder(event) {
    const target = event.target;
    const el = target.closest('.folderTree__link');

    if (el) {
      const isOpen = el.classList.contains('ft-is-folderOpen')
      const noClick = el.classList.contains('ft-no-click');

      if (!noClick) {
        const els = document.querySelectorAll('.folderTree__link');
        if (els) {
          els.forEach(el => {
            el.classList.remove('ft-is-selected');
            if (!el.classList.contains('ft-has-child')) {
              el.classList.remove('ft-is-folderOpen');
            }
          });
        }
        el.classList.add('ft-is-selected');
        if (isOpen && (!el.classList.contains('ft-is-root')) ) {
          el.parentNode.querySelector('.folderTree__ul').remove();
        } else {
          el.classList.add('ft-is-folderOpen');
        }
        const elId = el.getAttribute('id');
        const getLoData = Config.apiUrl + 'lms/lo/get&id=' + elId;
        axios.get(getLoData).then( (response) => {
          const child = Tree(response);
          const childView = Content(response);
          const folderView = document.querySelector('.folderView');
          const inputParent = document.querySelector('#treeview_selected_organization');
          inputParent.value = elId;
          if (!el.classList.contains('ft-is-root')) {
            el.insertAdjacentHTML('afterend',child);
          }
          folderView.innerHTML = childView;
          contextMenu();
          initSortable();
          initDragDrop();
        }).catch( (error) => {
          console.log(error)
        });
        event.preventDefault();
      }
    }

  }

  renameEl() {
    const rename = document.querySelector('.folderTree__rename');
    const input = document.querySelector('.folderTree__rename__input');
    const value = input.value;
    const el = input.parentNode.parentNode;
    const elId = el.getAttribute('id');
    const renameLoData = Config.apiUrl + 'lms/lo/rename&id=' + elId + '&newName=' + value;

    axios.get(renameLoData).then().catch( (error) => {
      console.log(error);
    });

    rename.classList.remove('is-show');
    el.childNodes[0].innerHTML = value;
    el.classList.remove('ft-no-click');

    document.querySelector('.folderView').querySelector('.folderView__li[data-id="' + elId + '"]').querySelector('.folderView__label').innerHTML = value;
  }

}

function initSortable() {
  const listRoot = document.querySelector('.js-folder-root');
  const list = document.querySelectorAll('.js-sortable-tree');
  const view = document.querySelector('.js-sortable-view');

  if (listRoot) {
    new Sortable.create(listRoot, {});
  }

  if (view) {
    new Sortable.create(view, {
      draggable: '.folderView__li',
      animation: 150,
      easing: 'cubic-bezier(1, 0, 0, 1)',
      fallbackOnBody: true,
      invertSwap: true,
      swapThreshold: 0.43,
      onUpdate: function (evt) {
        const currentElement = evt.item;
        const currentElementId = currentElement.id;
        const parentElement = document.querySelector('.ft-is-selected');
        const childElement = document.querySelector('.folderView__ul').querySelectorAll('.folderView__li');
        const childElementArray = [];
        let parentElementId = 0;
        console.log('current: ' + currentElementId);

        if (parentElement) {
          parentElementId = parentElement ? parentElement.id : 0;
          console.log('parent: ' + parentElementId);
        }

        childElement.forEach(el => {
          const elId = el.id;
          childElementArray.push(elId);
        });

        console.log('child order: ' + childElementArray);

        const reorderLoData = Config.apiUrl + 'lms/lo/reorder&id=' + currentElementId + '&newParent=' + parentElementId + '&newOrder=' + childElementArray;
        axios.get(reorderLoData).then().catch( (error) => {
          console.log(error);
        });
      },
    });
  }

  if (list.length > 0) {
    list.forEach(single => {
      new Sortable.create(single, {
        group: 'nested',
        draggable: '.folderTree__li',
        animation: 150,
        easing: 'cubic-bezier(1, 0, 0, 1)',
        fallbackOnBody: true,
        swapThreshold: 0.62,
        onEnd: function (evt) {
          const currentElement = evt.item;
          const currentElementId = currentElement.id;
          const parentElement = currentElement.parentNode.closest('.ft-is-parent');
          const childElement = currentElement.closest('.folderTree__ul').querySelectorAll('.folderTree__li');
          const childElementArray = [];
          let parentElementId = 0;
          console.log('current: ' + currentElementId);

          if (parentElement) {
            parentElementId = parentElement ? parentElement.id : 0;
            console.log('parent: ' + parentElementId);
          }

          childElement.forEach(el => {
            const elId = el.id;
            childElementArray.push(elId);
          });

          console.log('child order: ' + childElementArray);

          const reorderLoData = Config.apiUrl + 'lms/lo/reorder&id=' + currentElementId + '&newParent=' + parentElementId + '&newOrder=' + childElementArray;
          axios.get(reorderLoData).then().catch( (error) => {
            console.log(error);
          });

          evt.to;    // target list
          evt.from;  // previous list
          evt.oldIndex;  // element's old index within old parent
          evt.newIndex;  // element's new index within new parent
          evt.oldDraggableIndex; // element's old index within old parent, only counting draggable elements
          evt.newDraggableIndex; // element's new index within new parent, only counting draggable elements
          evt.clone // the clone element
          evt.pullMode;  // when item is in another sortable: `"clone"` if cloning, `true` if moving
        },
      })
    });
  }
}


function contextMenu() {
  contextmenu('.folderTree__link:not(.ft-is-root)', (target) => {
    return [
      {
        text: 'Rinomina',
        onClick() {
          const rename = document.querySelector('.folderTree__rename');
          const renameInput = document.querySelector('.folderTree__rename__input');

          if (target.classList.contains('folderTree__rename__input') === false) {
            if (target.hasAttribute('id')) {
              target.classList.add('ft-no-click');
              target.appendChild(rename);
            } else {
              target.parentNode.classList.add('ft-no-click');
              target.parentNode.appendChild(rename);
            }
            rename.classList.add('is-show');
            renameInput.focus();
            renameInput.setAttribute('value', target.textContent);

            // Rendo tutti gli elementi non cliccabile se sono in modalità rinomina
            const elsNotClick = document.querySelectorAll('.ft-no-click');
            if (elsNotClick) {
              for (let el of elsNotClick) {
                el.addEventListener('click', (e) => {
                  e.preventDefault();
                })
              }
            }

            // Stop della propagazione del click se sono su context menu, in alternativa disabilito modifica input se clicco fuori dall'input
            document.addEventListener('click', (event) => {
              if (event.detail) { // fix trigger click se premo su spazio
                const clickInside = rename.contains(event.target);
                if (event.target.classList.contains('menu-item-clickable')) {
                  event.stopPropagation();
                } else {
                  if (!clickInside) {
                    renameInput.blur();
                    rename.classList.remove('is-show');
                  }
                }
              }
            });
          }

        }
      },
      {
        text: 'Elimina',
        onClick() {
          let siblings;
          let elId;

          if (target.hasAttribute('id')) {
            siblings = target.parentNode.children;
            target.parentNode.querySelector('.folderTree__link').remove();
            elId = target.getAttribute('id');
          } else {
            siblings = target.parentNode.parentNode.children;
            target.parentNode.parentNode.querySelector('.folderTree__link').remove();
            elId = target.parentNode.getAttribute('id');
          }

          document.querySelector('.folderView').querySelector('.folderView__li[data-id="' + elId + '"]').parentNode.remove();

          if (siblings) {
            for (let el of siblings) {
              if (el.classList.contains('folderTree__ul')) {
                el.classList.remove('folderTree__ul');
              }
            }
          }

          const deleteLoData = Config.apiUrl + 'lms/lo/delete&id=' + elId;
          axios.get(deleteLoData).then().catch( (error) => {
            console.log(error);
          });

        }
      }
    ]
  })
}

function initDragDrop() {
  const contexts = document.querySelectorAll('.js-folder-tree-view');
  let currentEl, currentElId;

  contexts.forEach(context => {
    context.addEventListener('drag', (event) => {
      if (event.target.classList.contains('is-file')) {
        currentEl = event.target;
        currentElId = currentEl.id;
      }
    });

    context.addEventListener('dragenter', (event) => {
      const target = event.target;

      if (currentEl) {
        if ( (currentElId !== target.id) && (target.classList.contains('is-dropzone')) ) {
          target.classList.add('fv-is-dropped');
        }
      }
    });

    context.addEventListener('dragleave', (event) => {
      const target = event.target;

      if (currentEl) {
        if ((currentElId !== target.id) && (target.classList.contains('is-dropzone'))) {
          target.classList.remove('fv-is-dropped');
        }
      }
    });

    context.addEventListener('drop', (event) => {
      const target = event.target;
      target.classList.remove('fv-is-dropped');

      if (currentEl) {
        if ( (currentElId !== target.id) && (target.classList.contains('is-dropzone')) ) {
          const reorderLoData = Config.apiUrl + 'lms/lo/reorder&id=' + currentElId + '&newParent=' + event.target.id;
          axios.get(reorderLoData).then(() => {
            currentEl.style.display = 'none';
          }).catch((error) => {
            console.log(error);
          });
        }
      }
    });
  });
}

export default FolderTree
