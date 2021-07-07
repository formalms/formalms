/**
 * FormaDropZoneHelpers
 */
import DropzoneTwig from './../twig/dropzone.html.twig';

// document.querySelector('#drop-zone').FormaFileUploader();

 class _FormaDropZoneHelpers {

  constructor() {
    this.Id = 0;
    console.log(DropzoneTwig);
  }

  GenerateId() {
    return ++this.Id;
  }

  FormatBytes(bytes, decimals = 2) {
    if (bytes === 0) {
      return '0 Bytes';
    }
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
  }
}

// Act as static class, while its not
if(!window.FormaDropZoneHelpers) {
  window.FormaDropZoneHelpers = new _FormaDropZoneHelpers(); // Static properties are not available in some browsers
}


/*
 * object.watch polyfill
 *
 * 2012-04-03
 *
 * By Eli Grey, http://eligrey.com
 * Public Domain.
 * NO WARRANTY EXPRESSED OR IMPLIED. USE AT YOUR OWN RISK.
 */
// object.watch
if (!Object.prototype.watch) {
	Object.defineProperty(Object.prototype, 'watch', {
    enumerable: false
		, configurable: true
		, writable: false
		, value: function (prop, handler) {
			var
      oldval = this[prop]
			, newval = oldval
			, getter = function () {
				return newval;
			}
			, setter = function (val) {
				oldval = newval;
				return newval = handler.call(this, prop, oldval, val);
			};
			if (delete this[prop]) { // can't watch constants
				Object.defineProperty(this, prop, {
          get: getter
					, set: setter
					, enumerable: true
					, configurable: true
				});
			}
		}
	});
}
// object.unwatch
if (!Object.prototype.unwatch) {
	Object.defineProperty(Object.prototype, 'unwatch', {
    enumerable: false
		, configurable: true
		, writable: false
		, value: function (prop) {
			var val = this[prop];
			delete this[prop]; // remove accessors
			this[prop] = val;
		}
	});
}


/**
 * FormaDropZone
 */
class FormaDropZone {

  constructor(idOrClassOrElement = null, options =  {}) {
    // Properties
    this.Name = 'FormaDropZone';
    this.Id = window.FormaDropZoneHelpers.GenerateId();
    this.FilesList = [];
    this.Element = null;
    this.FileInput = null;
    this.Tmp = {};
    this.SelectedIndexFile = null;
    this.Options = {
      ListWrapper: '#drop-zone-list',
      SubmitText: 'Upload files',
      OnUploadClick: null
    };

    // FilesList watcher
    this.watch('FilesList', this.OnFilesListChange);

    Object.assign(this.Options, options);

    this.ListTemplate = document.createElement('div'); // Template for list
    this.ListTemplate.innerHTML = `\
    <div class="fd-list">\
      <div class="columns">\
        <div class="fd-list-column fd-list-files-column"></div>\
        <div class="fd-list-column fd-edit-column"></div>\
      </div>\
      <div class="form-group"><button type="submit">${this.Options.SubmitText}</button></div>\
    </div>`;

    // Init
    if(!idOrClassOrElement) {
      this.Error(`constructor() -> undefined target reference ${idOrClassOrElement}`);
    } else {
      // Setup
      this.Element = typeof idOrClassOrElement === 'object' ? idOrClassOrElement : document.querySelector(idOrClassOrElement);
      this.Element.classList.add('forma-drop-zone');
      // Create internal body
      this.Element.appendChild(this.HTMLBody());
      // Create file input
      this.Element.appendChild(this.HTMLInputFile());
      // d&d events
      ['dragenter', 'dragleave', 'dragover', 'drop'].forEach(eventName => {
        if(eventName === 'drop') {
          this.Element.addEventListener(eventName, (event) => { 
            this[eventName.charAt(0).toUpperCase() + eventName.slice(1)](event)
          }, false);
        } else {
          this.Element.addEventListener(eventName, (event) => {
            event.preventDefault()
          });
        }
      });
      // On click active fileInput
      this.Element.addEventListener('click', () => {
        this.FileInput.click();
      });
      // Get files on change
      this.FileInput.addEventListener('change', (e) => {
        this.FilesList = this.FilesList.concat(Array.from(e.target.files));
        this.RenderList();
        this.SelectFile(this.FilesList.length - 1);
      });
    }
  }

  get FileEditColumn() {
    return document.querySelector('.fd-edit-column');
  }

  OnFilesListChange(id, oldList, newList) {
    const list = Array.from(newList);
    list.forEach(item => {
      if(!item.hasOwnProperty('title') && !item.hasOwnProperty('description')) {
        item.title = item.name;
        item.description = '';
      }
    })
    return list;
  }

  Drop(event) {
    event.preventDefault();
    this.FilesList = this.FilesList.concat(Array.from(event.dataTransfer.files));
    this.RenderList();
    this.SelectFile(this.FilesList.length - 1);
  }

  HTMLBody() {
    const middleWrapper = document.createElement('div');
    middleWrapper.classList.add('fd-middle-wrapper');
    const spanGuide = document.createElement('span');
    spanGuide.innerText = 'Browse or drop here the files';
    middleWrapper.appendChild(spanGuide);
    return middleWrapper;
  }

  HTMLInputFile() {
    const hiddenFile = document.createElement('input');
    hiddenFile.type = 'file';
    hiddenFile.multiple = true;
    hiddenFile.hidden = true;
    hiddenFile.id = `fd-file-${this.Id}`;
    this.FileInput = hiddenFile;
    return hiddenFile;
  }

  /**
   * Render list and attach events
   * @param {} filesList 
   * @returns 
   */
  RenderList(filesList = this.FilesList) {

    // Destroy view
    this.Tmp.ListContainerDestination = document.querySelector(this.Options.ListWrapper);
    this.Tmp.ListContainerDestination.innerHTML = '';

    if(!filesList.length) {
      return;
    }

    // Template clone
    var templateClone = this.ListTemplate.cloneNode(true);

    // Files wrapper
    const fileListWrapper = templateClone.querySelector('.fd-list-files-column');
    const fileEditWrapper = templateClone.querySelector('.fd-edit-column');
    const SubmitButton = templateClone.querySelector('button[type="submit"]');
  
    //Create edit form
    fileEditWrapper.innerHTML = '<div>\
      <div class="form-group title-input-wrapper"><label>Title</label><input type="text" class="title"/></div>\
      <div class="form-group description-input-wrapper"><label>Description</label><textarea class="description"></textarea></div>\
    </div>';

    // Creat files list
    filesList.forEach((file, index) => {
      const fileNode = document.createElement('div');
      fileNode.classList.add('fd-list-file');
      fileNode.innerHTML = `\
        <div class="file-detail">\
          <div class="name">${file.title}</div>\
          <div class="size">${window.FormaDropZoneHelpers.FormatBytes(file.size, 2)}</div>\
          </div><div class="action-delete"><div class="button">X</div>\
        </div>`;

      fileListWrapper.appendChild(fileNode);
      // Delete
      fileNode.querySelector('.action-delete').addEventListener('click', () => {
        this.FilesList.splice(index, 1);
        this.FilesList = [ ...this.FilesList ];
        this.RenderList();
        if(this.FilesList.length) {
          this.SelectFile(0);
        }
      })
      // Edit
      fileNode.querySelector('.file-detail').addEventListener('click', () => {
        this.SelectFile(index);
      });
      // On title change
      fileEditWrapper.querySelector('.title-input-wrapper input.title')
      .addEventListener('keyup', (event) => {
        fileListWrapper.querySelectorAll('.file-detail .name')[this.SelectedIndexFile].innerText = event.target.value;
        this.FilesList[this.SelectedIndexFile].title = event.target.value;
      });
      // On description change
      fileEditWrapper.querySelector('.description-input-wrapper textarea.description')
      .addEventListener('keyup', (event) => {
        this.FilesList[this.SelectedIndexFile].description = event.target.value;
      });
    });

    // On submit click
    SubmitButton.addEventListener('click', () => {
      if(this.Options.OnUploadClick) {
        this.Options.OnUploadClick(this.FilesList);
      }
    });

    this.Tmp.ListContainerDestination.appendChild(templateClone);

    return templateClone;
  }

  SelectFile(index) {
    this.SelectedIndexFile = index;
    this.FileEditColumn.querySelector('.title-input-wrapper input.title').value = this.FilesList[index].title;
    this.FileEditColumn.querySelector('.description-input-wrapper textarea.description').value = this.FilesList[index].description;
  }

  Error(message = '') {
    throw `${this.Name}: ${message}`
  }

}

/**
 * Javascript plugin FormaFileUploader
 */
Element.prototype.FormaFileUploader = function() {
  new FormaDropZone(this);
}