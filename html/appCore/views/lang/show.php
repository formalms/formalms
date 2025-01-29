<?php FormaLms\lib\Get::title(Lang::t('_LANGUAGE', 'admin_lang')); ?>

<table class="table table-bordered display" style="width:100%" id="langlist"></table>
<br>
<a id="addlang_bottom" href="#" onclick="openAddFunc()" class="ico-wt-sprite subs_add" title="<?php echo Lang::t('_ADD', 'standard'); ?>"><span><?php echo Lang::t('_ADD', 'standard'); ?></span></a>
<a href="index.php?r=adm/lang/import" class="ico-wt-sprite subs_import" title="<?php echo Lang::t('_IMPORT', 'standard'); ?>"><span><?php echo Lang::t('_IMPORT', 'standard'); ?></span></a>
<a href="index.php?r=adm/lang/clearCache" class="ico-wt-sprite subs_import" title="<?php echo Lang::t('_CLEAR_CACHE', 'standard'); ?>"><span><?php echo Lang::t('_CLEAR_CACHE', 'standard'); ?></span></a>


<!-- Modal add -->
<div class="modal" id="addModal" style="display: none; z-index: 9999;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body" data-val="body">
            </div>
            <div class="modal-footer">
                <div class="alert alert-danger text-center" style="display: none;" data-val="message"></div>
                <button type="button" class="btn btn-default" data-val="cancel"><?php echo Lang::t('_CANCEL', 'standard'); ?></button>
                <button type="button" class="btn btn-primary" data-val="submit"><?php echo Lang::t('_SAVE', 'standard'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Modal edit -->
<div class="modal" id="modModal" style="display: none; z-index: 9999;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body" data-val="body">
            </div>
            <div class="modal-footer">
                <div class="alert alert-danger text-center" style="display: none;" data-val="message"></div>
                <button type="button" class="btn btn-default" data-val="cancel"><?php echo Lang::t('_CANCEL', 'standard'); ?></button>
                <button type="button" class="btn btn-primary" data-val="submit"><?php echo Lang::t('_SAVE', 'standard'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Modal confirm -->
<div class="modal" id="confirmModal" style="display: none; z-index: 9999;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body" data-val="body">
            </div>
            <div class="modal-footer">
                <div class="alert alert-danger text-center" style="display: none;" data-val="message"></div>
                <button type="button" id="yes" class="btn btn-primary" data-val="yes"><?php echo Lang::t('_YES', 'standard'); ?></button>
                <button type="button" class="btn btn-default" data-val="no"><?php echo Lang::t('_NO', 'standard'); ?></button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var openAddFunc = function () {
        var modalSelector = '#addModal';
        var modal = $(modalSelector);
        var fClose = function () {
            modal.modal("hide");
        };
        $.ajax({
            type: 'GET',
            url: 'ajax.adm_server.php?r=adm/lang/addmask',
            success: function (data) {
                var form = JSON.parse(data).body;
                modal.find('*[data-val=body]').html(form);
                modal.modal("show");
                modal.find("*[data-val=cancel]").unbind().bind("click", fClose);
                modal.find("*[data-val=submit]").unbind().bind("click", addFunc);
            }
        });
    };

    var addFunc = function () {
        var modalSelector = '#addModal';
        var modal = $(modalSelector);
        var msg = modal.find('*[data-val=message]');
        msg.html('').hide();

        $.ajax({
            type: 'POST',
            url: 'ajax.adm_server.php?r=adm/lang/insertlang',
            data: modal.find('form').serialize(),
            success: function (data) {
                data = JSON.parse(data);
                if (data.success) {
                    location.reload();
                } else {
                    console.log(data.message, "data err")
                    console.log(msg, "msg")
                    msg.html(data.message).show();
                }
            }
        });
    };

    var openModFunc = function (id) {
        var modalSelector = '#modModal';
        var modal = $(modalSelector);
        var fClose = function () {
            modal.modal("hide");
        };
        $.ajax({
            type: 'GET',
            url: 'ajax.adm_server.php?r=adm/lang/mod',
            data: {
                lang_code: id
            },
            success: function (data) {
                var form = JSON.parse(data).body;
                modal.find('*[data-val=body]').html(form);
                modal.modal("show");
                modal.find("*[data-val=cancel]").unbind().bind("click", fClose);
                modal.find("*[data-val=submit]").unbind().bind("click", modFunc);
            }
        });
    };

    var modFunc = function () {
        var modalSelector = '#modModal';
        var modal = $(modalSelector);
        var msg = modal.find('*[data-val=message]');
        msg.html('').hide();

        $.ajax({
            type: 'POST',
            url: 'ajax.adm_server.php?r=adm/lang/updatelang',
            data: modal.find('form').serialize(),
            success: function (data) {
                data = JSON.parse(data);
                if (data.success) {
                    location.reload();
                } else {
                    msg.html(data.message).show();
                }
            }
        });
    };

    var confirmDialog = function (modalSelector, message, onConfirm) {
        var modal = $(modalSelector);
        var fClose = function () {
            modal.modal("hide");
        };
        modal.modal("show");
        modal.find("*[data-val=body]").empty().append(message);
        modal.find("*[data-val=yes]").unbind().one('click', onConfirm).one('click', fClose);
        modal.find("*[data-val=no]").unbind().one("click", fClose);
    };

    var delFunc = function (id) {
        confirmDialog("#confirmModal", "<?php echo Lang::t('_AREYOUSURE', 'standard'); ?>", function () {
            $.ajax({
                type: 'POST',
                url: 'ajax.adm_server.php?r=adm/lang/del',
                data: {
                    lang_code: id
                },
                success: function (data) {
                    location.reload();
                }
            });
        });
    };

    $(function () {
        var body = <?php echo json_encode($langList); ?>;

        var columns = [{
            data: 'lang_code',
            title: '<?php echo Lang::t('_LANGUAGE', 'admin_lang'); ?>',
            sortable: true
        },
            {
                data: 'lang_description',
                title: '<?php echo Lang::t('_DESCRIPTION', 'admin_lang'); ?>',
                sortable: true
            },
            {
                data: 'lang_direction',
                title: '<?php echo Lang::t('_ORIENTATION', 'admin_lang'); ?>',
                sortable: true
            },
            {
                data: 'lang_stats',
                title: '<?php echo Lang::t('_STATISTICS', 'admin_lang'); ?>',
                sortable: true
            },
            {
                data: 'lang_translate',
                title: '<span class="ico-sprite subs_elem"><span><?php echo Lang::t('_TRANSLATELANG', 'admin_lang'); ?></span></span>',
                sortable: true
            },
            {
                data: 'lang_diff',
                title: '<span class="ico-sprite subs_diff"><span><?php echo Lang::t('_DIFF_LANG', 'admin_lang'); ?></span></span>',
                sortable: true
            },
            {
                data: 'lang_export',
                title: '<span class="ico-sprite subs_download"><span><?php echo Lang::t('_EXPORT_XML', 'admin_lang'); ?></span></span>',
                sortable: true
            },
            {
                data: 'lang_mod',
                title: '<span class="ico-sprite subs_mod"><span><?php echo Lang::t('_MOD', 'admin_lang'); ?></span></span>',
                sortable: true
            },
            {
                data: 'lang_del',
                title: '<span class="ico-sprite subs_del"><span><?php echo Lang::t('_DEL', 'admin_lang'); ?></span></span>',
                sortable: true
            }

        ];
        var rows = [];

        body.forEach(function (item, k) {
            link = '<a id="' + item.id + '" href="' + item.lang_translate + '" class="ico-sprite subs_elem" title="<?php echo Lang::t('_TRANSLATELANG', 'admin_lang'); ?>"><span></span></a>'
            item.lang_translate = link;
            link = '<a id="' + item.id + '" href="' + item.lang_diff + '" class="ico-sprite subs_diff" title="<?php echo Lang::t('_DIFF_LANG', 'admin_lang'); ?>"><span></span></a>'
            item.lang_diff = link;
            link = '<a id="' + item.id + '" href="' + item.lang_export + '" class="ico-sprite subs_download" title="<?php echo Lang::t('_EXPORT_XML', 'admin_lang'); ?>"><span></span></a>'
            item.lang_export = link;
            link = '<a id="' + item.id + '" href="#" onclick="openModFunc(\'' + item.lang_code + '\')" class="ico-sprite subs_mod" title="<?php echo Lang::t('_MOD', 'admin_lang'); ?>"><span></span></a>'
            item.lang_mod = link;
            link = '<a id="' + item.id + '" href="#" onclick="delFunc(\'' + item.lang_code + '\')" class="ico-sprite subs_del" title="<?php echo Lang::t('_DEL', 'admin_lang'); ?>"><span></span></a>'
            item.lang_del = link;

            rows.push(Object.assign({}, item));
        });

        t = $('#langlist').FormaTable({
            rowId: function (row) {
                return row[0];
            }, // cambia
            scrollX: true,
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            columns,
            data: rows,
            dom: 'Bfrtip',
            stateSave: true,
            deferRender: true,
        });
    });
</script>
</div>
