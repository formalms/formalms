


var stringify = function(o) {
	var temp = '{';
	var fields = [];
	var def;
	for (x in o) {
		try { def = o[x].toString(); } catch(e) { def = '[not valid]'; }
		fields.push(x+': '+def);
	}
	temp += fields.join(', ');
	temp += '}';
	return temp;
}


function initTable() {

    // Column definitions
    var myColumnDefs = [ // sortable:true enables sorting
        {key:"id", label:"ID", sortable:true},
        {key:"name", label:"Name", sortable:true},
        {key:"firstname", label:"First name", sortable:true},
        {key:"lastname", label:"Last name", sortable:true},
        {key:"email", label:"Email", sortable:true}
    ];


    // DataSource instance
    var myDataSource = new YAHOO.util.DataSource("ajax.adm_server.php?mn=_test_module&plf=framework&");
    myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
    myDataSource.responseSchema = {
        resultsList: "records",
        fields: [
            {key:"id", parser:"number"},
            {key:"name"},
            {key:"firstname"},
            {key:"lastname"},
            {key:"email"}
        ],
        metaFields: {
            totalRecords: "totalRecords" // Access to value in the server response
        }
    };

    // DataTable configuration
    var myConfigs = {
        initialRequest: "sort=id&dir=asc&startIndex=0&results=15", // Initial request for first page of data
        dynamicData: true, // Enables dynamic server-driven data
        sortedBy : {key:"id", dir:YAHOO.widget.DataTable.CLASS_ASC}, // Sets UI initial sort arrow
        paginator: new YAHOO.widget.Paginator({ rowsPerPage:15 }) // Enables pagination
    };

    // DataTable instance
    var myDataTable = new YAHOO.widget.DataTable("datatable", myColumnDefs, myDataSource, myConfigs);
    // Update totalRecords on the fly with value from server
    myDataTable.handleDataReturnPayload = function(oRequest, oResponse, oPayload) {
        oPayload.totalRecords = oResponse.meta.totalRecords; alert(stringify(oRequest)+"\n\n\n"+stringify(oResponse)+"\n\n\n"+stringify(oPayload)+"\n\n\n"+stringify(oPayload.pagination));
        return oPayload;
    }

}
