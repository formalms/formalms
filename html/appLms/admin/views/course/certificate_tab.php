 <?php
    echo '<script type="text/javascript">';
    // init lanaguage strng
     echo '_start="'.Lang::t('_STARTS_WITH', 'standard').'"';
     echo '_contain="'.Lang::t('_CONTAINS','standard').'"';
     echo '_equal="'.Lang::t('_EQUAL','standard').'"';
     

 ?>

 
 
 function addSearch(table_name){
     $('#'+table_name+' thead tr').clone(true).appendTo( '#'+table_name+' thead' );
     // adding searchng function
     
     
     
     
 }
 
 
 
 $(document).ready(function() {

    
    $('#table_certificate thead tr').clone(true).appendTo( '#table_certificate thead' );
    $('#table_certificate thead tr:eq(1) th').each( function (i) {
            var title = $(this).text();
            
            if(((i>1 && i<=7) || i>=12  )) {
                $(this).html( '<select id="sel_' + i + '" name="sel_' + i + '"><option value="0">_start</option><option selected value="1">_contain</option><option value="2">_equal</option></select>  <input id="input_' + i + '" name="input_' + i + '" type="text" size=38 placeholder=""  />' );
            }  else{
                $(this).html( '' );            
            } 
      

            if((i>=8 && i<=10)) {
                $(this).html( '<input id="input_' + i + '" name="input_' + i + '" type="text" size=38 placeholder="" />' );
            }           
 
 
 
 
            // INPUT SEARCH
            $( 'input', this ).on( 'keyup change', function () {
                      
                var cond =  $('select[name=sel_' + i + ']').val() 
         
                str_search = "" + this.value + "";
                if(cond==0) str_search = "^" + this.value;
                if(cond==1) str_search =  this.value;
                if(cond==2) str_search = "^" + this.value + "^";
                     
                    // alert( i + " - " + str_search )     
                               
                if ( table.column(i).search( str_search ,true, false) !== this.value ) {
                    table
                        .column(i)
                        .search( str_search)
                        .draw();
                }
            
            } );
            
            
    } );
 
 

 
 
    var $table_n = $('#table_certificate');
    tipoCorso = $table_n.data( "tipocorso");
    id_course = $table_n.data( "id_course");
 
    visEdizione = false
    if(tipoCorso=="classroom") visEdizione = true  
                            
    var table = $table_n.DataTable( {
        orderCellsTop: true,

        stateSave: false, 
        
        'language': {
                            'lengthMenu': 'Visualizza _MENU_ certificati per pagina',
                            'zeroRecords': 'Nessun certificato ',
                            'print':'Stampa',
                            'info': 'Numero pagina _PAGE_ di _PAGES_',
                            'infoEmpty': 'Nessun certificato disponibile',
                            'infoFiltered': '(filtrato da _MAX_ certificati totale)'     ,
                            'sEmptyTable' :   'Nessun certificato nella tabella',
                            'sInfo'  :  'Vista da _START_ a _END_ di _TOTAL_ certificati',
                            'sSearch'  :  'Cerca:',
                            'pagingType': 'full_numbers',
                            'oPaginate': {
                                   'sFirst':'Inizio',
                                   'sPrevious' :   'Precedente',
                                   'sNext':    'Successivo',
                                   'sLast' :    'Fine'
                                   
                                  }
                        } ,        
        
        "columnDefs": [
            {
                "targets": [ 0 ],
                "visible": false,
                "searchable": false    ,
                "orderable": false ,
            },
            
            {
                "targets": [ 1 ],
                "visible": true,
                "searchable": false,
                "orderable": false ,
                "className": 'select-checkbox' ,
                'checkboxes': {
                    'selectRow': false
                }
            }             
            
            ,
            {
                "targets": [7 ],
                "orderable": true ,
                "visible": visEdizione,  
            }              
               ,
            {
                "targets": [ 11 ],
                "orderable": false
            }             
               ,
               
                 
            {
                "targets": [ 12 ],
                "visible": false,
                "searchable": false    ,
                "orderable": false ,
            } 
            
        ] ,
          dom: 'Bfrtip',
          select: {
            style: 'multi',
            selector: 'td:first-child'
          },
        
        buttons: [
            {
                
                extend: 'selectAll',
                className: 'selectall',
                text: "Seleziona tutto"    ,
                action : function(e) {
                    
                   sel_all = $('#sel_all').val(); 
                    
                     // SELECTED NOTHING
                    if(sel_all=="false"){
                        
                        $('#sel_all').val("true");
                        
                        e.preventDefault();
                        table.rows({ page: 'all'}).nodes().each(function() {
                           $(this).removeClass('selected')
                        })
                        table.rows({ search: 'applied'}).nodes().each(function() {
                           $(this).addClass('selected');        
                        })
                        
                        
                        table.buttons( [0] ).text( function ( dt, button, config ) {
                            return dt.i18n( 'buttons.input', 'Seleziona nessuno' );
                        } );   
                        
                        
                         if ($("th.select-checkbox").hasClass("selected")) {
                                example.rows().deselect();
                                $("th.select-checkbox").removeClass("selected");
                            } else {
                                example.rows().select();
                                $("th.select-checkbox").addClass("selected");
                            }                        
                        
                                               
                        
                    } 
                    
                     // SELECTED ALL TRUE
                    if(sel_all=="true"){
                        $('#sel_all').val("false");
                        table.rows().deselect();
                        table.buttons( [0] ).text( function ( dt, button, config ) {
                            return dt.i18n( 'buttons.input', 'Seleziona tutto' );
                        } );                          
                        
                    }    
                    
                    
                }                
                
                
            },
  
            {
                
                text: "Nascondi Ricerca"    ,
                action: function () {
                    
                    show_search = $('#show_search').val();
        
                    if(show_search=="true"){
                        $('#table_certificate thead tr:eq(1) th').toggle(false);
                        $('#show_search').val("false");

                        table.buttons( [1] ).text( function ( dt, button, config ) {
                            return dt.i18n( 'buttons.input', 'Visualizza Ricerca' );
                        } );                         
                    
                    }
                    
             
                    if(show_search=="false"){
                        $('#table_certificate thead tr:eq(1) th').toggle(true);
                        $('#show_search').val("true");

                        table.buttons( [1] ).text( function ( dt, button, config ) {
                            return dt.i18n( 'buttons.input', 'Nascondi Ricerca' );
                        } );                         
                    
                    }                    
                    
                }              
              
                
            }           
                       
            ,                        
                    
              {  
                    extend: 'colvis',
                    text : "Visualizza colonne" ,
                    columnText: function ( dt, idx, title ) {
                        return title;
                    } ,
                    columns:':gt(1)'
                    
              }  ,           
            
       

            
             
            {
                text: "Reset filtri"    ,
                action: function () {
                   // table.rows().deselect();
                   //alert("reset filtri")
                    $('input').each(function(index,data) {
                        var value = $(this).val();
                       // alert(index + " - " + value)
                        //$(this).val('');
                        $("#input_" + index).val("");
                        
                       table
                        .column(index)
                        .search( "")
                        .draw();                        
                        
                        
                    });  
                    
                    table.search( "" ).draw();            
                    
      
                }
            }
                      
        
            
        ],        
         
        'order': [[ 2, 'desc' ]],

    } );
    
                  
 
    show_search = $('#show_search').val();
            
    if(show_search=="false"){
        // HIDE SEARCH
        $('#table_certificate thead tr:eq(1) th').toggle(false);
        $('#show_search').val("false");

        table.buttons( [1] ).text( function ( dt, button, config ) {
            return dt.i18n( 'buttons.input', 'Visualizza Ricerca' );
        } );          
        
        
        // HIDE CUSTOM FIELD
        table.columns( '.custom' ).visible( false );
                       
    
    } 
 
    
} );   // DOCUMENT READY


 $(document).on('click', '#select_all_1', function() { 
      $('input').prop('checked', true);
  });
  
  
   $(document).on('click', '#unselect_all_1', function() {
      $('input').prop('checked', false);
    
    
  });
  
  
  
  
   // select rows selected
  function getRowsSelected(){
      
            
        table = $('#table_certificate').DataTable(); 

        var data = table.rows('.selected').data();
        var newarray=[];       
                for (var i=0; i < data.length ;i++){
                   newarray.push(data[i][0] + "-" + data[i][12] + "-" + id_course);          
                }
         
        var sData = newarray.join();                    
        return sData;      
      
  }  
  
  
  
  function print_certificate(id_user, id_course, id_certificate){
      var posting = $.get(
                    'index.php',
                    {
                        modname:'certificate',
                        of_platform:'lms',
                        op:'print_certificate',
                        certificate_id: id_certificate,
                        course_id: id_course,
                        user_id: id_user
                    }
                );
                posting.done(function (responseText) { 
                    location.reload();    
                });
                posting.fail(function () {
                    alert("Error generating certificate");
                })      

  }

  
  // generate  selected certificates
  function generate_all_certificate(){
  
    strRows =   getRowsSelected();
    if(strRows=="") return 

    var x = document.getElementById("loading");
    x.style.display = "block";
    
    var arr_users = strRows.split(',');
                       
    $.each(arr_users, function( index, value ) {
        
        var arr_user = value.split("-");
        var id_user = arr_user[0];
        var id_certificate = arr_user[1];
        var id_course = arr_user[2];
        
        print_certificate(id_user, id_course, id_certificate) ;
        
    });
    
  }
  
  
  // download  selected  certificates
  function download_all_certificate(){
      
    strRows =   getRowsSelected();
    if(strRows=="") return 
    var arr_users = strRows.split(',');
    document.location.href = "index.php?modname=certificate&of_platform=lms&op=download_all&str_rows=" + strRows;
      
  }

  <?  echo '</script'   ?>
  