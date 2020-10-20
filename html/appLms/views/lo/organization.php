<?php echo Form::openForm("orgshow" , "index.php?modname=storage&op=display", false, "POST"); ?>
    <table id="tree" style="width: 100%; margin-top: 20px;">
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
            console.log(data.node.data.image_type)
            return data.node.data.image_type + '-icon'
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

            let playEl = node.data.actions['play']
            if (playEl) {
                $("<a>")
                    .attr("href", playEl.url)
                    .append(
                        $("<img>").attr("src", playEl.image)
                    )
                    .appendTo($tdList.eq(1))
            }
            
            let editEl = node.data.actions['edit']
            if (editEl) {
                $("<a>")
                    .attr("href", editEl.url)
                    .append(
                        $("<img>").attr("src", editEl.image)
                    )
                    .appendTo($tdList.eq(2))
                /* $("<input>")
                    .attr("type", "image")
                    .attr("src", editEl.image)
                    .attr("name", editEl.link)
                    .appendTo($tdList.eq(2)) */
            }

            let copyEl = node.data.actions['copy']
            if (copyEl) {
                $("<input>")
                    .attr("type", "image")
                    .attr("src", copyEl.image)
                    .attr("name", copyEl.link)
                    .appendTo($tdList.eq(3))
            }

            let propertiesEl = node.data.actions['properties']
            if (propertiesEl) {
                $("<input>")
                    .attr("type", "image")
                    .attr("src", propertiesEl.image)
                    .attr("name", propertiesEl.link)
                    .appendTo($tdList.eq(4))
            }

            let categorizeEl = node.data.actions['categorize']
            if (categorizeEl) {
                $("<input>")
                    .attr("type", "image")
                    .attr("src", categorizeEl.image)
                    .attr("name", categorizeEl.link)
                    .appendTo($tdList.eq(5))
            }

            let accessEl = node.data.actions['access']
            if (accessEl) {
                $("<input>")
                    .attr("type", "image")
                    .attr("src", accessEl.image)
                    .attr("name", accessEl.link)
                    .appendTo($tdList.eq(6))
            }

            let downEl = node.data.actions['down']
            if (downEl) {
                $("<input>")
                    .attr("type", "image")
                    .attr("src", downEl.image)
                    .attr("name", downEl.link)
                    .appendTo($tdList.eq(7))
            }

            let upEl = node.data.actions['up']
            if (upEl) {
                $("<input>")
                    .attr("type", "image")
                    .attr("src", upEl.image)
                    .attr("name", upEl.link)
                    .appendTo($tdList.eq(8))
            }

            let moveEl = node.data.actions['move']
            if (moveEl) {
                $("<input>")
                    .attr("type", "image")
                    .attr("src", moveEl.image)
                    .attr("name", moveEl.link)
                    .appendTo($tdList.eq(9))
            }

            let deleteEl = node.data.actions['delete']
            if (deleteEl) {
                $("<input>")
                    .attr("type", "image")
                    .attr("src", deleteEl.image)
                    //.attr("name", deleteEl.link)
                    .click(function(e) {
                        e.preventDefault()
                        $.post(deleteEl.url).success(console.log)
                    })
                    .appendTo($tdList.eq(10))
            }

            let renameEl = node.data.actions['rename']
            if (renameEl) {
                $("<input>")
                    .attr("type", "image")
                    .attr("src", renameEl.image)
                    //.attr("name", renameEl.link)
                    .click(function(e) {
                        e.preventDefault()
                        var newName = prompt('input new name')
                        $.post(renameEl.url + '&newName=' + newName).success(console.log)
                    })
                    .appendTo($tdList.eq(1))
            }

            let moveFolderEl = node.data.actions['moveFolder']
            if (moveFolderEl) {
                $("<input>")
                    .attr("type", "image")
                    .attr("src", moveFolderEl.image)
                    //.attr("name", moveFolderEl.link)
                    .click(function(e) {
                        e.preventDefault()
                        var newParentId = prompt('input new parent id')
                        $.post(moveFolderEl.url + '&newParentId=' + newParentId).success(console.log)
                    })
                    .appendTo($tdList.eq(9))
            }
        }
    });
</script>