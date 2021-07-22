/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

function DynamicFilterOtherFieldTypes(oConfig) {

  var oScope = this;

  this._oLangs = new LangManager();

  this._oLanguageList = [];
  this.setLanguageList = function(langs) { oScope._oLanguageList = langs; };
  
  this._oCourseList = [];
  this.setCourseList = function(courses) { oScope._oCourseList = courses; };

	this._oLevelsList = [];
  this.setLevelsList = function(levels) { oScope._oLevelsList = levels; };

  if (oConfig.languages) this.setLanguageList(oConfig.languages);
  if (oConfig.courses) this.setCourseList(oConfig.courses);
	if (oConfig.levels) this.setLevelsList(oConfig.levels);
  
  this.getFieldTypesList =  function() { return oScope._fieldTypes; };
  
  
  this._fieldTypes = [
    //course status
    {
      type: "coursestatus",
      
      getValue: function(id_sel, id_filter) {
          var o, id = this.type + "_" + id_filter + "_" + id_sel, $D = YAHOO.util.Dom;
          o = [$D.get(id+"_sel").value, $D.get(id).value];
          return o.join(",");
      },
        
      setValue: function(id_sel, id_filter, newValue) {
          if (!newValue)
            o = {cond: 0, value: ""};
          else
            o = newValue.split(',');
          var i, s, id = this.type + "_"+id_filter+"_"+id_sel, $D = YAHOO.util.Dom;
          $D.get(id).value = o[1];
          s = $D.get(id+"_sel");
          for (i=0; i<s.options.length; i++) {
            if (s.options[i].value == o[0]) {
              s.selectedIndex = i;
              break;
            }
          }
      },
        
      render: function(id_sel, id_filter, oEl, id_field) {
          var i, t = document.createElement("SELECT"), d = document.createElement("DIV"), s = document.createElement("SELECT");
          d.className = this.type + "_container";
          t.id = this.type + "_"+id_filter+"_"+id_sel;
          t.className = "filter_value";
          s.className = "condition_select";
          s.id = t.id+"_sel";
          
          s.options[0] = new Option(oScope._oLangs. def('_NOT_STARTED'),0);
          s.options[1] = new Option(oScope._oLangs. def('_IN_ITINERE'),1);
          s.options[2] = new Option(oScope._oLangs. def('_COMPLETED'),2);
          
          for (i=0; i<oScope._oCourseList.length; i++) {
            opt = document.createElement("OPTION");
            opt.value = oScope._oCourseList[i].id;
            opt.text = oScope._oCourseList[i].value;
            try { t.add(opt, null); } catch(e) { t.add(opt); }
          }          
          
          oEl.appendChild(s);
          oEl.appendChild(document.createTextNode(" "));
          oEl.appendChild(t);
      }
    },
  
  
    //language
    {
       type: "language",
      
        getValue: function(id_sel, id_filter) {
          return YAHOO.util.Dom.get(this.type+"_"+id_filter+"_"+id_sel).value;
        },
        
        setValue: function(id_sel, id_filter, newValue) {
          if (!newValue) newValue=0;
          var i, s = YAHOO.util.Dom.get(this.type+"_"+id_filter+"_"+id_sel);
          for (i=0; i<s.options.length; i++) {
            if (s.options[i].value == newValue) {
              s.selectedIndex = i;
              break;
            }
          }
        },
        
        render: function(id_sel, id_filter, oEl, id_field) {
          var i, opt, sons = oScope._oLanguageList, s = document.createElement("SELECT"), d = document.createElement("DIV");

          s.className = "dropdown_filter_value";
          if (id_field.split("_")[0] == "std") return; //at the moment this type is not allowed for standard fields
          d.className = this.type + "_container";
          s.id = this.type+"_"+id_filter+"_"+id_sel;          
          
          for (i=0; i<sons.length; i++) {
            opt = document.createElement("OPTION");
            opt.value = sons[i].id;
            opt.text = sons[i].value;
            try { s.add(opt, null); } catch(e) { s.add(opt); }
          }
          d.appendChild(s);
          oEl.appendChild(d);  
        }

    },

		//levels
    {
       type: "adminlevels",

        getValue: function(id_sel, id_filter) {
          return YAHOO.util.Dom.get(this.type+"_"+id_filter+"_"+id_sel).value;
        },

        setValue: function(id_sel, id_filter, newValue) {
          if (!newValue) newValue=0;
          var i, s = YAHOO.util.Dom.get(this.type+"_"+id_filter+"_"+id_sel);
          for (i=0; i<s.options.length; i++) {
            if (s.options[i].value == newValue) {
              s.selectedIndex = i;
              break;
            }
          }
        },

        render: function(id_sel, id_filter, oEl, id_field) {
          var i, opt, sons = oScope._oLevelsList, s = document.createElement("SELECT"), d = document.createElement("DIV");

          s.className = "dropdown_filter_value";
          if (id_field.split("_")[0] == "std") return; //at the moment this type is not allowed for standard fields
          d.className = this.type + "_container";
          s.id = this.type+"_"+id_filter+"_"+id_sel;

          for (i=0; i<sons.length; i++) {
            opt = document.createElement("OPTION");
            opt.value = sons[i].id;
            opt.text = sons[i].value;
            try { s.add(opt, null); } catch(e) { s.add(opt); }
          }
          d.appendChild(s);
          oEl.appendChild(d);
        }

    }
  ];
}