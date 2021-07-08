
import DropzoneTwig from './../twig/dropzone.html.twig';
import FormaPlugin from './FormaPlugin';


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

  get FileEditColumn() {
    return this.Element.querySelector('.fd-edit-column');
  }

  get FileNodes() {
    return this.Element.querySelectorAll('.fd-list-file');
  }

  get SubmitButton() {
    return this.Element.querySelector('[type="submit"]');
  }

  constructor(idOrClassOrElement = null, options =  {}) {
    super();
    // Properties
    this.Name = 'FormaDropZone';
    this.FilesList = [];
    this.Element = null;
    this.SelectedIndexFile = null;
    this.Options = {
      ListWrapper: '#drop-zone-list',
      SubmitText: 'Upload files',
      OnSubmitClick: null
    };

    // FilesList watcher
    this.watch('FilesList', this.OnFilesListChange);

    Object.assign(this.Options, options);
    
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
    this.DropZone.removeEventListener('click', () => {});
    this.DropZone.addEventListener('click', () => {
      this.FileInput.click();
    });
    // ... on file selection change rendere the list
    this.FileInput.removeEventListener('change', () => {});
    this.FileInput.addEventListener('change', (e) => {
      this.FilesList = this.FilesList.concat(Array.from(e.target.files));
      this.RenderList();
      this.SelectFile(this.FilesList.length - 1);
    });
    // Drag events
    ['dragenter', 'dragleave', 'dragover', 'drop'].forEach(eventName => {
      if(eventName === 'drop') {
        this.DropZone.addEventListener(eventName, (event) => { 
          this[eventName.charAt(0).toUpperCase() + eventName.slice(1)](event)
        }, false);
      } else {
        this.DropZone.addEventListener(eventName, (event) => {
          event.preventDefault()
        });
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
          _dropzoneInstance.FilesList = [ ..._dropzoneInstance.FilesList ];
          _dropzoneInstance.RenderList();
          if(_dropzoneInstance.FilesList.length) {
            _dropzoneInstance.SelectFile(0);
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
          this.Options.OnSubmitClick(this.FilesList);
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
    this.FileEditColumn.querySelector('.title-input-wrapper input.title').value = this.FilesList[index].title;
    this.FileEditColumn.querySelector('.description-input-wrapper textarea.description').value = this.FilesList[index].description;
  }

  Error(message = '') {
    throw `${this.Name}: ${message}`
  }

  /**
   * Render list and attach events
   * @param {} filesList 
   * @returns 
   */
  RenderList(filesList = this.FilesList) {
    const view = DropzoneTwig({
      files: filesList.map(file => { file.formatted_size = this.FormatBytes(file.size); return file; }),
      submitText: this.Options.SubmitText
    });
    this.Element.innerHTML = view;
    this.AttachEvents();
    return view;
  }

}

/**
 * Javascript plugin FormaFileUploader
 */
Element.prototype.FormaFileUploader = function(options) {
  new FormaDropZone(this, options);
}