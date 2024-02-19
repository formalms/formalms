import Content from '../twig/content.html.twig';
import Sortable from 'sortablejs/modular/sortable.complete.esm.js';
import LearningView from '../modules/Base/LearningView';
import Lang from './../helpers/Lang';

const axios = require('axios');

class FolderView extends LearningView {


    constructor(baseApiUrl, controller, type, disabledActions, folderTreeInstance, selectEventListener = 'click', clickEventListener = 'click', dblClickEventListener = 'dblclick') {
        super();
        const _this = this;

        _this.baseApiUrl = baseApiUrl;
        _this.controller = controller;
        _this.type = type;
        _this.isReady = false;
        _this.selectedId = folderTreeInstance.getSelectedId();

        _this.disabledActions = disabledActions;
        _this.filterDBClickEvents = [];

        _this.container = document.querySelector('*[data-container=' + _this.type + ']');
        if (selectEventListener) {
            _this.container.addEventListener(selectEventListener, (e) => {
                _this.toggleSelectEl(e);
            });
        }
        if (clickEventListener) {
            _this.container.addEventListener(clickEventListener, (e) => {
                _this.triggerClick(e);
            });
        }
        if (dblClickEventListener) {
            _this.container.addEventListener(dblClickEventListener, (e) => {
                _this.triggerDblClick(e);
            });
        }
        // Tap
        _this.onClickOrTap(_this.container, (e) => {
            _this.triggerDblClick(e)
        }, true /** only tap */);

        _this.emptySelectedItems();

        _this.container.addEventListener('createTreeItem', (e) => {
            if (_this.isReady) {
                _this.refresh(e.detail.selectedId);
            }
        });
        _this.container.addEventListener('openDir', (e) => {
            if (_this.isReady) {
                _this.refresh(e.detail.selectedId);
            }
        });
        _this.container.addEventListener('refreshTree', (e) => {
            if (_this.isReady) {
                _this.refresh(e.detail.selectedId);
            }
        });
        _this.container.addEventListener('folderTreeIsReady', (e) => {
            _this.refresh(e.detail.selectedId);
            _this.isReady = true;
        });
		
       _this.container.addEventListener('keydown', (e) => {
			if (event.keyCode === 13) {
				_this.triggerDblClick(e);
			}	
		});					

    }


    getContainer() {
        return this.container;
    }

    getType() {
        return this.type;
    }

    refresh(parentId) {
        const _this = this;

        if (parentId >= 0) {
            _this.selectedId = parentId;
        }

        _this.getData(_this.getApiUrl('get', {id: _this.selectedId}));
    }

    getSelectedItems() {
        return this.selectedItems;
    }

    emptySelectedItems() {
        this.selectedItems = {};
        this.container = this.getContainer();

        // Unselect all
        this.container.querySelectorAll('.fv-is-selected').forEach((item) => {
            item.classList.remove('fv-is-selected');
        });
    }

    toggleSelectedItem(el, id, notEmpty) {
        const li = el.closest('.folderView__li');

        if (!li) {
            return;
        }

        if (notEmpty) {
            this.selectedItems[id] = !this.selectedItems[id];

            if (this.selectedItems[id]) {
                li.classList.add('fv-is-selected');
            } else {
                li.classList.remove('fv-is-selected');
            }
        } else {
            this.emptySelectedItems();
            this.selectedItems[id] = true;
            li.classList.add('fv-is-selected');
        }
        return this.selectedItems[id];
    }

    toggleSelectEl(e) {
        const el = e.target;
        this.toggleSelectedItem(el, el.getAttribute('data-id'), (e.ctrlKey || e.metaKey));
    }

    initSortable() {
        const _this = this;
        const view = this.container.querySelector('.js-sortable-view');

        if (view) {
            new Sortable.create(view, {
                draggable: '.folderView__li',
                dataIdAttr: 'data-id',
                multiDrag: true, // Enable the plugin
                // multiDragKey: 'Meta', // Fix 'ctrl' or 'Meta' button pressed
                selectedClass: 'fv-is-selectedx',
                animation: 150,
                easing: 'cubic-bezier(1, 0, 0, 1)',
                fallbackOnBody: true,
                invertSwap: true,
                swapThreshold: 0.13,
                onUpdate: function (evt) {
                    const currentElement = evt.item;
                    const currentElementId = currentElement.getAttribute('data-id');

                    // Get parent dir from selected tree element
                    const parentElement = _this.container.querySelector('.ft-is-parent .ft-is-selected');
                    const parentElementId = parentElement ? parentElement.getAttribute('data-id') : 0;
                    const childrenUl = _this.container.querySelector('.folderView__ul');
                    if (!childrenUl) {
                        return;
                    }
                    const childElement = childrenUl.querySelectorAll('.folderView__li');
                    const childElementArray = [];

                    if (currentElementId == parentElementId) {
                        return;
                    }

                    childElement.forEach(el => {
                        const elId = el.getAttribute('data-id');
                        if (parentElementId != elId) {
                            childElementArray.push(elId);
                        }
                    });

                    _this.reorderData(_this.getApiUrl('reorder', {
                        id: currentElementId,
                        newParent: parentElementId,
                        newOrder: childElementArray
                    }));
                }
            });
        }
    }

    async reorderData(endpoint) {
        const _this = this;
        try {
            await axios.get(endpoint).then(() => {
                _this.getData(_this.getApiUrl('get'), {id: _this.selectedId});

                // dispatch reorderData event
                _this.container.dispatchEvent(new CustomEvent('reorderData'));
            }).catch((error) => {
                console.log(error);
            });
        } catch (e) {
            console.log(e);
        }
    }

    async getData(endpoint) {
        const _this = this;
        try {
            const params = _this.selectedId ? {id: _this.selectedId} : null;

            await axios.get(endpoint, params).then((response) => {
               
                if(response.data.currentState !== undefined) {
                    document.getElementById(`treeview_state_${_this.type}`).value = response.data.currentState;
                
                }
                const childView = Content({
                  
                    items: response.data,
                    isStudent: document.querySelector('.fv-is-student-area') ? true : false
                });
                const folderView = _this.container.querySelector('.folderView');
                folderView.innerHTML = childView;


                // Direct play a course If student user and direct-play class exists in LO element
                let isStudent = !!document.querySelector('.fv-is-student-area');
                if (isStudent) {
                    const elDplay = _this.container.querySelector('.direct-play');
                    if (elDplay) {
                        elDplay.closest('.js-folderView-file').click();
                    }
                }


                if (!document.querySelector('.js-disable-sortable')) {
                    _this.initSortable();
                }
                if (!_this.container.querySelector('.js-disable-context-menu')) {
                    // dispatch refreshContextMenu event
                    document.dispatchEvent(new CustomEvent('refreshContextMenu', {detail: {controller: _this.controller}}));
                }

                if (_this.disabledActions) {
                    const buttons = document.querySelectorAll('.folderView__actions');
                    buttons.forEach((folderButtons) => {
                        folderButtons.classList.add('hidden');
                    });
                }
            }).catch((error) => {
                console.log(error)
            });
        } catch (e) {
            console.log(e);
        }
    }

    triggerClick(e) {
        const el = e.target;
        const _this = this;

        if (el) {
            if (el.tagName === 'BUTTON' && el.getAttribute('type') !== 'submit') {
                e.preventDefault();
            }

            const li = el.closest('.folderView__li');

            if (!li) {
                return;
            }
            const elId = li.getAttribute('data-id');

            if (!elId) {
                return;
            }

            if (el.classList.contains('fv-is-delete')) {
                e.preventDefault();
                const deleteElement = Lang.Translation('_DELETE_ELEMENT_FOLDERVIEW', 'learningObjectContextMenu');
                if (confirm(deleteElement)) {
                    const deleteLoData = _this.getApiUrl('delete', {id: elId});
                    axios.get(deleteLoData).then(() => {
                        const elTree = _this.container.querySelector('.folderTree__li[data-id="' + elId + '"]');
                        if (elTree) {
                            elTree.remove();
                        }
                        const el = _this.container.querySelector('.folderView__li[data-id="' + elId + '"]');
                        if (el) {
                            el.remove();
                        }

                        // dispatch deletedItem event
                        _this.container.dispatchEvent(new CustomEvent('deleteTreeItem', {
                            detail: {selectedId: elId,}
                        }));
                    }).catch((error) => {
                        console.log(error);
                    });
                }
            }
            if (el.classList.contains('fv-is-copy')) {
                li.classList.add('is-ready-for-copy');
            }
        }
    }

    /*selectItems() {
      this.container.querySelectorAll('.folderView__li').forEach((item) => {
        item.classList.remove('fv-is-selected');
        const item_id = parseInt(item.getAttribute('data-id'));

        if (this.currentElsIds.includes(item_id)) {
          item.classList.add('fv-is-selected');
        }
      });
    }*/

    triggerDblClick(e) {
        let el = e.target;
        const _this = this;

        if (el.classList.contains('actions-wrapper')) {
            el = el.closest('li')
        }

        if (el) {
            const li = el.closest('.folderView__li');


            if (!li) {
                return;
            }
            const elId = li.getAttribute('data-id');

            if (!elId) {
                return;
            }

            let proceed = true;
            _this.filterDBClickEvents.forEach(event => {
                if (proceed) {
                    proceed = event(el);
                }
            });

            if (!proceed) {
                return;
            }

            if (el.classList.contains('js-folderView-folder')) {
                // It's dir
                _this.selectedId = elId;
                _this.refresh();
            } else if (el.classList.contains('js-folderView-file')) {
                // It's object
                if (el.querySelector('.fv-is-play').hasAttribute('href')) {
                    window.location = el.querySelector('.fv-is-play').getAttribute('href');
                } else {
                    el.querySelector('.fv-is-play').click();
                }
            }
        }
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

export default FolderView;
