<?php

include Forma::inc(_lib_ . '/formatable/include.php');

echo getTitleArea(Lang::t('_MY_CERTIFICATE', 'certificate'));

$cert_columns = [
    ['key' => 'on_date', 'label' => Lang::t('_DATE'), 'className' => 'min-cell', 'sortable' => true],
    ['key' => 'code', 'label' => Lang::t('_CODE')],
    ['key' => 'course_name', 'label' => Lang::t('_COURSE', 'certificate')],
    ['key' => 'cert_name', 'label' => Lang::t('_CERTIFICATE_NAME', 'course')],
    ['key' => 'date_complete', 'label' => Lang::t('_DATE_COMPLETE', 'certificate')],
    ['key' => 'download', 'label' => Lang::t('_TAKE_A_COPY', 'certificate'), 'className' => 'img-cell'],
];

// tabs
$selected_tab = FormaLms\lib\Get::req('current_tab', DOTY_STRING, 'cert');
$tabs_header = '<ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active" ' . ($selected_tab == 'cert' ? ' class="selected"' : '') . '>
                        <a href="#cert" aria-controls="cert" role="tab" data-toggle="tab"><em>' . Lang::t('_CERTIFICATE', 'menu') . '</em>
                        </a>
                    </li>
                    <li role="presentation" ' . ($selected_tab == 'meta' ? ' class="selected"' : '') . '>
                        <a href="#meta" aria-controls="meta" role="tab" data-toggle="tab"><em>' . Lang::t('_TITLE_META_CERTIFICATE', 'certificate') . '</em>
                        </a>
                    </li>
               </ul>';

?>

    <div class="std_block">
        <?php echo $tabs_header; ?>
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane fade in active" id="cert">
                <table class="table table-striped table-bordered display" style="width:100%" id="mycertificates">
                  <thead>
                    <tr><?php
                      foreach ($cert_columns as $column) {?>
                        <th scope="col"><b><?php echo $column['label']; ?></b></th><?php
                      }?>
                    </tr>
                  </thead>
                </table>
            </div><!-- close tabs -->
     
            <div role="tabpanel" class="tab-pane fade in " id="meta">
                    <table class="table table-striped table-bordered display" style="width:100%" id="mymetacertificates">
                    </table>
            </div> <!-- close tabs -->
        </div> <!-- close content -->
    </div> <!-- close std_block -->    

<script>

$("body").on("click", "#pdf_download", function () {
    $(this).attr("title", "<?php echo Lang::t('_DOWNLOAD'); ?>")
    $(this).html("<span><?php echo Lang::t('_DOWNLOAD'); ?></span>")
});



$(function() {
  var tableId = '#mycertificates';
  var languageObj = {
                    'sInfo' :"<?php echo Lang::t('_FROM'); ?> _START_  <?php echo Lang::t('_TO'); ?> _END_ <?php echo Lang::t('_OF'); ?>   _TOTAL_ ",
                    'infoEmpty': '',
                    'sEmptyTable' : '<?php echo Lang::t('_NO_CERT_AVAILABLE', 'certificate'); ?> '
                 } 
  

  $(tableId).FormaTable({
    processing: true,
    serverSide: true,
    scrollX: true,
    order: [[ 0, "asc" ]],
    ajax: {
      url: 'ajax.server.php?r=mycertificate/getMyCertificates',
      type: "POST",
      complete: function(json) {},
    },
    language:languageObj
  });

  
  
  var metacert_tb =  $('#mymetacertificates').FormaTable({
       data:  <?php echo $metacertificates; ?>,
       columns:[
       { data: 'id_certificate', title: 'id_certificate', sortable: false, visible: false, searchable: false },
       { data: 'idAssociation', title: 'idAssociation', sortable: false, visible: false, searchable: false },
       { data: 'on_date', title: '<?php echo Lang::t('_DATE'); ?>', sortable: true, visible: true, searchable: false, render: function(data){ 
           if (data !=  '' && data != '0000/00/00') {
               d = new Date(data)
               return d.toLocaleDateString()
           } 
           return '';   
       }},
       { data: 'code', title: '<?php echo Lang::t('_CODE'); ?>', sortable: true, visible: true, searchable: false },
       { data: 'name', title: '<?php echo Lang::t('_CERTIFICATE_NAME', 'course'); ?>', sortable: true, visible: true, searchable: true },
       { data: 'course_name', title: '<?php echo Lang::t('_COURSES'); ?>', sortable: true, visible: true, searchable: true, render: function(data){return data.split("|").join("<br>");} },
       { data: 'path_name', title: '<?php echo Lang::t('_COURSEPATH'); ?>', sortable: true, visible: true, searchable: true, render: function(data){return data.split("|").join("<br>");} },
       { data: 'cert_file', title: '<?php echo Lang::t('_TAKE_A_COPY', 'certificate'); ?>', 
                sortable: false, visible: true, searchable: false, render: function(data, type, row){
            title = (data!=''?"<?php echo LANG::t('_DOWNLOAD', 'certificate'); ?>":"<?php echo LANG::t('_GENERATE', 'certificate'); ?>");        
            return  '<a id="pdf_download" class="ico-wt-sprite subs_pdf" href="?r=mycertificate/downloadMetaCert'
                    +'&id_certificate='+row.id_certificate
                    +'&aggCert=1'
                    +'&id_association='+row.idAssociation + '" title="'+title+'">'
                    +title+'</a>' 
       }}
       ],
       language:languageObj
       
      
  })
  

});

</script>
