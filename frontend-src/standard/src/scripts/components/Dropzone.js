
import DropzoneTwig from './../twig/dropzone.html.twig';
import FormaPlugin from './FormaPlugin';
import Lang from './../helpers/Lang';


/**
 * FormaDropZone
 * ex: HTMLElement.FormaFileUploader();
 */
class FormaDropZone extends FormaPlugin {

  get FileInput() {
    return this.Element.querySelector('#fd-file-input');
  }

  get DropZone() {
    return this.Element.querySelector('#drop-zone');
  }

  get DropZoneList() {
    return this.Element.querySelector('#drop-zone-list');
  }

  get FileEditColumn() {
    return this.Element.querySelector('.fd-edit-column');
  }

  get FileNodes() {
    return this.Element.querySelectorAll('.fd-list-file');
  }

  get SubmitButton() {
    return this.Element.querySelector('[type="submit"]');
  }

  set Uploading(valBool) {
    this._Uploading = valBool;
    this.RenderList();
  }

  set Errors(errorsObj) {
    this._Errors = errorsObj;
    this.RenderList();
  }

  set SuccessMessage(text) {
    this._Uploading = false;
    this._ErrorMessage = null;
    this.Reset();
    this._SuccessMessage = text;
    this.RenderList();
  }

  set ErrorMessage(text) {
    this._Uploading = false;
    this._SuccessMessage = null;
    this.Reset();
    this._ErrorMessage = text;
    this.RenderList();
  }

  constructor(idOrClassOrElement = null, options =  {}) {
    super();
    // Properties
    this.Name = 'FormaDropZone';
    this.FilesList = [];
    this.Element = null;
    this._Errors = {};
    this._Uploading = false;
    this.SelectedIndexFile = null;
    this._SuccessMessage = null;
    this._ErrorMessage = null;
    this.Options = {
      ListWrapper: '#drop-zone-list',
      SubmitText: Lang.Translation('dropzone._UPLOAD_FILES'),
      OnSubmitClick: null
    };

    // FilesList watcher
    this.watch('FilesList', this.OnFilesListChange);

    Object.assign(this.Options, options);

    if(options.SubmitText) {
      this.Options.SubmitText = Lang.Translation(options.SubmitText);
    }

    // Init
    if(!idOrClassOrElement) {
      this.Error(`constructor() -> undefined target reference ${idOrClassOrElement}`);
    } else {
      this.Element = typeof idOrClassOrElement === 'object' ? idOrClassOrElement : document.querySelector(idOrClassOrElement);
      this.RenderList();
    }
  }

  AttachEvents() {
    // Click on element upload file selection
    if(this.DropZone) {
      this.DropZone.removeEventListener('click', () => {});
      this.DropZone.addEventListener('click', () => {
        this.FileInput.click();
      });
    }
    // ... on file selection change rendere the list
    if(this.FileInput) {
      this.FileInput.removeEventListener('change', () => {});
      this.FileInput.addEventListener('change', (e) => {
        this.FilesList = this.FilesList.concat(Array.from(e.target.files));
        this.RenderList();
        this.SelectFile(this.FilesList.length - 1);
      });
    }
    // Drag events
    ['dragenter', 'dragleave', 'dragover', 'drop'].forEach(eventName => {
      if(eventName === 'drop') {
        if(this.DropZone) {
          this.DropZone.addEventListener(eventName, (event) => { 
            this[eventName.charAt(0).toUpperCase() + eventName.slice(1)](event)
          }, false);
        }
      } else {
        if(this.DropZone) {
          this.DropZone.addEventListener(eventName, (event) => {
            event.preventDefault()
          });
        }
      }
    });
    // On click on file
    if(this.FilesList.length) {
      let _dropzoneInstance = this;
      // Select file
      this.Element.querySelectorAll('.fd-list-file').forEach((fileNode) => {
        fileNode.querySelector('.file-detail').addEventListener('click', function() {
          _dropzoneInstance.SelectFile(this.getAttribute('data-index'));
        });
        // Delete file
        fileNode.querySelector('.action-delete').addEventListener('click', function() {
          _dropzoneInstance.FilesList.splice(this.getAttribute('data-index'), 1);
          _dropzoneInstance.RemoveError(this.getAttribute('data-index'));
          _dropzoneInstance.FilesList = [ ..._dropzoneInstance.FilesList ];
          _dropzoneInstance.RenderList(this.FilesList, 0);
          if(_dropzoneInstance.FilesList.length) {
            setTimeout(() => {
              _dropzoneInstance.SelectFile(0);
            }, 500);
          }
        })
      })
      // On title change
      this.FileEditColumn.querySelector('.title-input-wrapper input.title')
      .addEventListener('keyup', (event) => {
        this.FileNodes[this.SelectedIndexFile].querySelector('.file-detail .name').innerText = event.target.value;
        this.FilesList[this.SelectedIndexFile].title = event.target.value;
      });
      // On description change
      this.FileEditColumn.querySelector('.description-input-wrapper textarea.description')
      .addEventListener('keyup', (event) => {
        this.FilesList[this.SelectedIndexFile].description = event.target.value;
      });
      this.SubmitButton.addEventListener('click', () => {
        if(this.Options.OnSubmitClick) {
          const obj = {
            formData: new FormData(),
            info: this.FilesList
          }
          this.FilesList.forEach((file, index) => {
            obj.formData.append(`file${index}`, file);
          });
          obj.formData.append('info', JSON.stringify(this.FilesList));
          this.Options.OnSubmitClick.call(this, obj);
        }
      });
    }
  }

  OnFilesListChange(id, oldList, newList) {
    const list = Array.from(newList);
    list.forEach(item => {
      if(!item.hasOwnProperty('title') && !item.hasOwnProperty('description')) {
        item.title = item.name;
        item.description = '';
      }
    })
    this._SuccessMessage = null;
    this._ErrorMessage = null;
    return list;
  }

  Drop(event) {
    event.preventDefault();
    this.FilesList = this.FilesList.concat(Array.from(event.dataTransfer.files));
    this.RenderList();
    this.SelectFile(this.FilesList.length - 1);
  }

  SelectFile(index) {
    this.SelectedIndexFile = index;
    if(this.DropZoneList.querySelector('.selected')) {
      this.DropZoneList.querySelector('.selected').classList.remove('selected');
    }
    this.FileNodes[index].classList.add('selected');
    this.FileEditColumn.querySelector('.title-input-wrapper input.title').value = this.FilesList[index].title;
    this.FileEditColumn.querySelector('.error').innerText = this.GetError(`file${index}`);
    this.FileEditColumn.querySelector('.description-input-wrapper textarea.description').value = this.FilesList[index].description;
  }

  RemoveError(index) {
    if(!this._Errors) {
      return null;
    } else {
      if(this._Errors && this._Errors.files) {
        Object.keys(this._Errors.files).forEach(keyFile => {
          if(`file${index}` === keyFile) {
            delete this._Errors.files[keyFile];
          }
        });
      }
    }
  }

  GetError(name) {
    if(!this._Errors) {
      return null;
    } else {
      return this._Errors.files ? this._Errors.files[name] ? this._Errors.files[name] : null : null;
    }
  }

  Reset() {
    this.FilesList = [];
    this._SuccessMessage = null;
    this._ErrorMessage = null;
    this._Uploading = false;
    this.SelectedIndexFile = null;
    this.RenderList();
  }

  /**
   * Render list and attach events
   * @param {} filesList 
   * @returns 
   */
  RenderList(filesList = this.FilesList, forceSelect = null) {
    const view = DropzoneTwig({
      errors: this._Errors,
      files: filesList.map((file, index) => { 
        file.formatted_size = this.FormatBytes(file.size); 
        file.indexedName = `file${index}`;
        return file; 
      }),
      submitText: this.Options.SubmitText,
      uploading: this._Uploading,
      success: this._SuccessMessage,
      error: this._ErrorMessage
    });
    this.Element.innerHTML = view;
    this.AttachEvents();
    if(forceSelect != null) {
      this.SelectFile(forceSelect);
    } else {
      if(null != this.SelectedIndexFile) {
        this.SelectFile(this.SelectedIndexFile);
      }
    }
    return view;
  }

}

/**
 * Javascript plugin FormaFileUploader
 */
Element.prototype.FormaFileUploader = function(options) {
  new FormaDropZone(this, options);
}

module.exports = FormaDropZone;