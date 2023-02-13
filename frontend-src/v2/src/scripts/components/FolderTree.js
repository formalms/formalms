import 'regenerator-runtime/runtime'
// const axios = require('axios');
import Tree from '../twig/tree.html.twig';
import FolderTreeBase from './FolderTreeBase';


class FolderTree extends FolderTreeBase {

  constructor(baseApiUrl, controller, type) {
    super(baseApiUrl, controller, type);
    /**
     * Tree twig resource
     */
    this.Tree = Tree;
  }

  static create(baseApiUrl, controller, type) {
    return new FolderTree(baseApiUrl, controller, type);
  }

}

export default FolderTree
