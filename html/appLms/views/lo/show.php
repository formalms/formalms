<table id="tree">
    <colgroup>
        <col width="30px">
        </col>
        <col width="30px">
        </col>
        <col width="*">
        </col>
        <col width="50px">
        </col>
        <col width="30px">
        </col>
    </colgroup>
    <thead>
        <tr>
            <th> </th>
            <th>#</th>
            <th>Name</th>
            <th>Custom Data</th>
            <th>Important</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>

<div class="TreeWindow"></div>

<script>
    $(".TreeWindow").TreeWindow({
        ajax: "index.php?r=lms/lo/get",
        folder_box: "<div><div class='title'></div></div>",
        element_box: "<div><div class='title'></div></div>",
        check_folder: function(data) {
            return data.is_folder
        },
        fill_box_callback: function(is_folder, view, data) {
            if (!is_folder) {
                if (data.link != "") {
                    view.find(".title").click(function() {
                        alert(data.id)
                    });
                }
            }
            view.find(".title").html(data.title)
        },
        success: function() {

            $(".TreeWindow").prepend("<div class='col-md-3'></div>");
            $(".TreeWindow").prepend("<div class='col-md-3'></div>");
        }
    });

    $("#tree").fancytree({
        titlesTabbable: true,
        table: {
            checkboxColumnIdx: 0, // render the checkboxes into the this column index (default: nodeColumnIdx)
            indentation: 16, // indent every node level by 16px
            nodeColumnIdx: 2 // render node expander, icon, and title to this column (default: #0)
        },
        gridnav: {
            autofocusInput: false, // Focus first embedded input if node gets activated
            handleCursorKeys: true // Allow UP/DOWN in inputs to move to prev/next node
        },
        icon: function(event, data) {
            if (data.node.isFolder()) {
                return "folder-icon";
            } else {
                return "file-icon";
            }
            // Otherwise no value is returned, so continue with default processing
        },
        extensions: ["edit", "filter", "table", "gridnav"],
        lazyLoad: function(event, data) {
            var node = data.node;
            // Load child nodes via Ajax GET /getTreeData?mode=children&parent=1234
            data.result = {
                url: "index.php?r=lms/lo/get",
                data: {
                    id: node.key
                },
                cache: false
            };
        },
        source: {
            url: "index.php?r=lms/lo/get",
            cache: false
        },
        postProcess: function(event, data) {
            data.result = data.response.map(function(v) {
                return {
                    data: v,
                    title: v.title,
                    key: v.id,
                    lazy: v.is_folder,
                    folder: v.is_folder,
                    active: false
                }
            })
        },
        renderColumns: function(event, data) {
            console.log('ciao')
            var node = data.node,
                $tdList = $(node.tr).find(">td");

            // Make the title cell span the remaining columns if it's a folder:
            if (node.isFolder()) {
                $tdList.eq(2)
                    .prop("colspan", 3)
                    .nextAll().remove();
                return;
            }
            // (Column #0 is rendered by fancytree by adding the checkbox)

            // Column #1 should contain the index as plain text, e.g. '2.7.1'
            $tdList.eq(1)
                .text(node.getIndexHier())
                .addClass("alignRight");
        }
    });
</script>