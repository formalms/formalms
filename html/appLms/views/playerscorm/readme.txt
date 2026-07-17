How to use:
- you can choose between standard and seamless styles simply by including the corresponding stylesheet in player.html
- load player.html into an iframe and set the required parameters
    - token: the access_token for server APIs call authorization
    - scorm: the ID of the SCORM to load into the player (corresponding to id_organization in database)
    - sco: (optional) the ID of the SCO to force the player to start with (corresponding to id_item in database)
- expose a closePlayer function in order to correctly handle player close
