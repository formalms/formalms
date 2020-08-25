<?php echo Form::openForm("orgshow" , "index.php?modname=storage&op=display", false, "POST"); ?>
    <table id="tree">
        <colgroup>
            <col width="*"></col>
            <col width="30px"></col>
            <col width="30px"></col>
            <col width="30px"></col>
            <col width="30px"></col>
            <col width="30px"></col>
            <col width="30px"></col>
            <col width="30px"></col>
            <col width="30px"></col>
        </colgroup>
        <thead>
            <tr>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
<?php echo Form::closeForm(); ?>
<script>

    $("#tree").fancytree({
        titlesTabbable: true,
        table: {
            checkboxColumnIdx: 0, // render the checkboxes into the this column index (default: nodeColumnIdx)
            indentation: 16, // indent every node level by 16px
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
            var node = data.node,
                $tdList = $(node.tr).find(">td");
            
            let index = 1
            for (var action in node.data.actions) {
                let element = node.data.actions[action]
                $("<input>")
                    .attr("type", "image")
                    .attr("src", element.image)
                    .attr("name", element.link)
                    .appendTo($tdList.eq(index))
                index++
            }
        }
    });
</script>