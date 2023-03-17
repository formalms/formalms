import 'regenerator-runtime/runtime'
import { contextmenu } from 'easycontext';
const axios = require('axios');
import Lang from './../helpers/Lang';

class ContextMenu {

  constructor(baseApiUrl) {
    this.baseApiUrl = baseApiUrl;
  }

  setContainerByTarget(target) {
    const _this = this;

    _this.container = target.closest('*[data-container]');
    _this.type = _this.container.getAttribute('data-container');
  }

  set(selector) {
    const _this = this;

    document.querySelectorAll('.context-menu').forEach((menu) => {
      menu.remove();
    });
    
    contextmenu(selector, (target) => {
      _this.setContainerByTarget(target);
      _this.currentEls = [];

      if(target.classList.contains('folderTree__link')) { // se clicco su una voce del folder tree
        _this.currentEls = Array.prototype.slice.call(_this.container.querySelectorAll('.ft-is-selected'));
      } else {
        _this.currentEls = Array.prototype.slice.call(_this.container.querySelectorAll('.fv-is-selected'));
      }

      // ? The object on which the actions of the context menu affect must be the object the menu is linked to, not the selected items
      const targetFound = _this.currentEls.find(element => element.getAttribute('data-id') === target.getAttribute('data-id'));
      if(!targetFound) {
        _this.currentEls.push(target);
      }

      _this.currentElsIds = [];
      _this.currentEls.forEach((item) => {
        _this.currentElsIds.push(parseInt(item.getAttribute('data-id')));
      });
      
      if (_this.currentElsIds.length == 0) {
        _this.currentElsIds = [parseInt(target.getAttribute('data-id'))];
      }

      const renameBtn = {
        text: Lang.Translation('_RENAME', 'learningObjectContextMenu'),
        onClick() {
          const renameOrig = document.querySelector('.folderTree__rename');
          const rename = renameOrig.cloneNode(true);
          const renameInput = rename.querySelector('.folderTree__rename__input');

          const btn = rename.querySelector('.js-ft-rename-el');
          const inputRename = rename.querySelector('.folderTree__rename__input');

          if (btn) {
            btn.addEventListener('click', () => {
              _this.renameEl();
            });
          }

          if (inputRename) {
            inputRename.addEventListener('keyup', (e) => {
              if (e.keyCode === 13) {
                _this.renameEl();
                e.preventDefault();
              }
            });
          }

          if (target.classList.contains('folderTree__rename__input') === false) {
            if (target.hasAttribute('data-id')) {
              target.classList.add('ft-no-click');
              target.appendChild(rename);
            } else {
              target.parentNode.classList.add('ft-no-click');
              target.parentNode.appendChild(rename);
            }
            rename.classList.add('is-show');  
            renameInput.focus();
            renameInput.value = target.getElementsByTagName('span')[0].outerText;
            renameInput.setAttribute('value', target.getElementsByTagName('span').outerText);

            // Rendo tutti gli elementi non cliccabili se sono in modalità rinomina
            const elsNotClick = _this.container.querySelectorAll('.ft-no-click');
            if (elsNotClick) {
              for (let el of elsNotClick) {
                el.addEventListener('click', (e) => {
                  e.preventDefault();
                })
              }
            }

            // Stop della propagazione del click se sono su context menu, in alternativa disabilito modifica input se clicco fuori dall'input
            _this.container.addEventListener('click', (event) => {
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
      };

      const copyBtn = {
        text: Lang.Translation('_COPY', 'learningObjectContextMenu'),
        onClick() {
          _this.currentElsIds.forEach((id) => {
            _this.container.querySelector('li[data-id="' + parseInt(id) + '"]').classList.add('is-ready-for-copy');
          });
          document.querySelector('.folderView__copyOverlay').classList.add('is-shown');
        }
      };

      const deleteBtn = {
        text: Lang.Translation('_DELETE', 'learningObjectContextMenu'),
        onClick() {
          let objectsNames = [];
          _this.currentEls.forEach(element => {
            if(element.classList.contains('folderTree__link')) {
              objectsNames.push(element.querySelector('span').outerText);
            } else {
              objectsNames.push(element.querySelector('.folderView__el span').outerText);
            }
          });
          if (confirm(`Sei sicuro di voler eliminare ${objectsNames.length === 1 ? 'l\'elemento' : 'gli elementi'} "${objectsNames.join(',')}"?`)) {
            const deleteData = _this.getApiUrl('delete', { ids: _this.currentElsIds });

            axios.get(deleteData).then(() => {
              _this.currentElsIds.forEach((elId) => {
                const elTree = _this.container.querySelector('.folderTree__li[data-id="' + elId + '"]');
                if (elTree) {
                  const ul = elTree.parentNode;
                  elTree.remove();

                  if (!ul.querySelector('li')) {
                    ul.remove();
                  }
                }
                const el = _this.container.querySelector('.folderView__li[data-id="' + elId + '"]');
                if (el) {
                  el.remove();
                  document.querySelector('.context-menu').classList.remove('menu-visible');
                }
                // dispatch deletedItem event
                _this.container.dispatchEvent(new CustomEvent('deleteTreeItem', {
                  detail: { selectedId: elId, }
                }));
              });
            }).catch((error) => {
              console.log(error);
            });
          }
        }
      };

      const foundFolder = _this.currentEls.find(element => element.classList.contains('js-folderView-folder'));
      let buttons = [];
      if(_this.currentElsIds.length <= 1) {
        buttons.push(renameBtn);
      }
      if(!foundFolder) {
        buttons.push(copyBtn);
      }
      buttons.push(deleteBtn);
      return buttons;
    });
  }

  setActiveTab() {
    const _this = this;

    _this.type = document.querySelector('.tab-link.active').getAttribute('data-type');
    _this.container = document.querySelector('*[data-container=' + _this.type + ']');
  }

  renameEl() {
    const _this = this;

    _this.setActiveTab();

    const rename = document.querySelector('.folderTree__rename.is-show');
    const input = rename.querySelector('.folderTree__rename__input');
    const value = input.value;
    const el = input.closest('.folderTree__li') ? input.closest('.folderTree__li') : input.closest('.folderView__li');
    const elId = el.getAttribute('data-id');
    const renameData = _this.getApiUrl('rename', { id: elId, newName: value });

    axios.get(renameData).then((res) => {
      if (res) {
        rename.classList.remove('is-show');
        const treeEl = _this.container.querySelector('.folderTree__link[data-id="' + elId + '"] span');
        if (treeEl) {
          treeEl.innerHTML = value;
          el.classList.remove('ft-no-click');
        }

        const li = _this.container.querySelector('.folderView__li[data-id="' + elId + '"]');
        if (li) {
          li.querySelector('.folderView__label').innerHTML = value;
        }

        rename.remove();
      }
    }).catch( (error) => {
      console.log(error);
    });
  }

  getApiUrl(action, params) {
    let url = `${this.baseApiUrl}/${action}`;
    if (!params) {
      params = {};
    }
    url += '&' + new URLSearchParams(params).toString();

    return url;
  }

}

export default ContextMenu
