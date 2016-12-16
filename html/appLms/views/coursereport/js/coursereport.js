window.CourseReport = (function($) {

  'use strict';

  var _users;
  var _data = {'courseID':'id'};
  var _loadingSnippet = 'Loading...';
  var _testId;


  var $detailsTab = $('.js-details');
  var $detailsTableRow;
  var $table;


  var fakeUsers;

  var userData = {
    "activities": [
      {
        "name": "Mychangemeter Corrado Santagati",
        "id": 1
      },
      {
        "name": "test vito",
        "id": 2
      }
    ],
    "students": [
      {
        "id": "11844",
        "userid": "/alessandro",
        "firstname": "Alessandro",
        "lastname": "Affronto",
        "email": "alessandro.affronto@purplenetwork.it",
        "register_date": "2016-02-10 17:39:12",
        "lastenter": "2016-12-15 18:13:46",
        "name": "Alessandro Affronto",
        "activities_results": [
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "75",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "81",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(17)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "81",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(21)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [],
          [
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            }
          ],
          [
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=116&idTrack=235&testName=test vito&studentName=alessandro",
              "active": "true"
            }
          ]
        ],
        "total_result": "90"
      },
      {
        "id": "12266",
        "userid": "/raffaella.airaghi",
        "firstname": "Raffaella",
        "lastname": "Airaghi",
        "email": "",
        "register_date": "2016-07-29 14:49:40",
        "lastenter": null,
        "name": "Raffaella Airaghi",
        "activities_results": [
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "75",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "81",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(17)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "81",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(21)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [],
          [
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=116&idTrack=235&testName=test vito&studentName=alessandro",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=116&idTrack=235&testName=test vito&studentName=alessandro",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ]
        ],
        "total_result": "90"
      },
      {
        "id": "11845",
        "userid": "/luigi",
        "firstname": "Luigi",
        "lastname": "Di Iorio",
        "email": "l.diiorio@openhs.it",
        "register_date": "2016-02-11 15:27:58",
        "lastenter": "2016-11-01 17:25:02",
        "name": "Luigi Di Iorio",
        "activities_results": [
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "75",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "81",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(17)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "81",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(21)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [],
          [
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=116&idTrack=235&testName=test vito&studentName=alessandro",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(0)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=180&testName=Mychangemeter Corrado Santagati&studentName=luigi",
              "active": "true"
            }
          ],
          [
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=116&idTrack=235&testName=test vito&studentName=alessandro",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(0)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=180&testName=Mychangemeter Corrado Santagati&studentName=luigi",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            }
          ]
        ],
        "total_result": "90"
      },
      {
        "id": "12290",
        "userid": "/luigi.iuso",
        "firstname": "Luigi",
        "lastname": "Iuso",
        "email": "",
        "register_date": "2016-09-08 09:44:29",
        "lastenter": "2016-10-28 19:36:07",
        "name": "Luigi Iuso",
        "activities_results": [
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "75",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "81",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(17)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "81",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(21)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [],
          [
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=116&idTrack=235&testName=test vito&studentName=alessandro",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(0)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=180&testName=Mychangemeter Corrado Santagati&studentName=luigi",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(8)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=233&testName=Mychangemeter Corrado Santagati&studentName=luigi.iuso",
              "active": "true"
            }
          ],
          [
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=116&idTrack=235&testName=test vito&studentName=alessandro",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(0)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=180&testName=Mychangemeter Corrado Santagati&studentName=luigi",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(8)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=233&testName=Mychangemeter Corrado Santagati&studentName=luigi.iuso",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ]
        ],
        "total_result": "90"
      },
      {
        "id": "12291",
        "userid": "/katia.maltoni",
        "firstname": "Katia",
        "lastname": "Maltoni",
        "email": "",
        "register_date": "2016-09-08 09:44:52",
        "lastenter": "2016-10-29 08:29:25",
        "name": "Katia Maltoni",
        "activities_results": [
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "75",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "81",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(17)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "81",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(21)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [],
          [
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=116&idTrack=235&testName=test vito&studentName=alessandro",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(0)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=180&testName=Mychangemeter Corrado Santagati&studentName=luigi",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(8)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=233&testName=Mychangemeter Corrado Santagati&studentName=luigi.iuso",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(10)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=232&testName=Mychangemeter Corrado Santagati&studentName=katia.maltoni",
              "active": "true"
            }
          ],
          [
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=116&idTrack=235&testName=test vito&studentName=alessandro",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(0)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=180&testName=Mychangemeter Corrado Santagati&studentName=luigi",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(8)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=233&testName=Mychangemeter Corrado Santagati&studentName=luigi.iuso",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(10)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=232&testName=Mychangemeter Corrado Santagati&studentName=katia.maltoni",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ]
        ],
        "total_result": "90"
      },
      {
        "id": "12268",
        "userid": "/massimiliano.olcese",
        "firstname": "Massimiliano",
        "lastname": "Olcese",
        "email": "",
        "register_date": "2016-07-29 14:53:24",
        "lastenter": null,
        "name": "Massimiliano Olcese",
        "activities_results": [
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "75",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "81",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(17)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "81",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(21)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [],
          [
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=116&idTrack=235&testName=test vito&studentName=alessandro",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(0)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=180&testName=Mychangemeter Corrado Santagati&studentName=luigi",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(8)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=233&testName=Mychangemeter Corrado Santagati&studentName=luigi.iuso",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(10)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=232&testName=Mychangemeter Corrado Santagati&studentName=katia.maltoni",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=116&idTrack=235&testName=test vito&studentName=alessandro",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(0)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=180&testName=Mychangemeter Corrado Santagati&studentName=luigi",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(8)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=233&testName=Mychangemeter Corrado Santagati&studentName=luigi.iuso",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(10)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=232&testName=Mychangemeter Corrado Santagati&studentName=katia.maltoni",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ]
        ],
        "total_result": "90"
      },
      {
        "id": "12289",
        "userid": "/manuela.parravicini",
        "firstname": "Manuela",
        "lastname": "Parravicini",
        "email": "",
        "register_date": "2016-09-08 09:44:11",
        "lastenter": "2016-10-28 18:37:26",
        "name": "Manuela Parravicini",
        "activities_results": [
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "75",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "81",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(17)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "81",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(21)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [],
          [
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=116&idTrack=235&testName=test vito&studentName=alessandro",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(0)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=180&testName=Mychangemeter Corrado Santagati&studentName=luigi",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(8)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=233&testName=Mychangemeter Corrado Santagati&studentName=luigi.iuso",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(10)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=232&testName=Mychangemeter Corrado Santagati&studentName=katia.maltoni",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(10)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=230&testName=Mychangemeter Corrado Santagati&studentName=manuela.parravicini",
              "active": "true"
            }
          ],
          [
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=116&idTrack=235&testName=test vito&studentName=alessandro",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(0)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=180&testName=Mychangemeter Corrado Santagati&studentName=luigi",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(8)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=233&testName=Mychangemeter Corrado Santagati&studentName=luigi.iuso",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(10)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=232&testName=Mychangemeter Corrado Santagati&studentName=katia.maltoni",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(10)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=230&testName=Mychangemeter Corrado Santagati&studentName=manuela.parravicini",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ]
        ],
        "total_result": "90"
      },
      {
        "id": "11836",
        "userid": "/admin",
        "firstname": "Alberto",
        "lastname": "Pastorelli",
        "email": "a.pastorelli@elearnit.net",
        "register_date": "0000-00-00 00:00:00",
        "lastenter": "2016-11-02 09:45:57",
        "name": "Alberto Pastorelli",
        "activities_results": [
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "75",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "81",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(17)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "81",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(21)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [],
          [
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=116&idTrack=235&testName=test vito&studentName=alessandro",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(0)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=180&testName=Mychangemeter Corrado Santagati&studentName=luigi",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(8)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=233&testName=Mychangemeter Corrado Santagati&studentName=luigi.iuso",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(10)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=232&testName=Mychangemeter Corrado Santagati&studentName=katia.maltoni",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(10)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=230&testName=Mychangemeter Corrado Santagati&studentName=manuela.parravicini",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=116&idTrack=235&testName=test vito&studentName=alessandro",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(0)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=180&testName=Mychangemeter Corrado Santagati&studentName=luigi",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(8)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=233&testName=Mychangemeter Corrado Santagati&studentName=luigi.iuso",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(10)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=232&testName=Mychangemeter Corrado Santagati&studentName=katia.maltoni",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(10)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=230&testName=Mychangemeter Corrado Santagati&studentName=manuela.parravicini",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            }
          ]
        ],
        "total_result": "90"
      },
      {
        "id": "12264",
        "userid": "/luca.rodighiero",
        "firstname": "Luca",
        "lastname": "Rodighiero",
        "email": "",
        "register_date": "2016-07-29 14:48:44",
        "lastenter": "2016-08-02 14:11:19",
        "name": "Luca Rodighiero",
        "activities_results": [
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "75",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "81",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(17)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "81",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(21)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [],
          [
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=116&idTrack=235&testName=test vito&studentName=alessandro",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(0)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=180&testName=Mychangemeter Corrado Santagati&studentName=luigi",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(8)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=233&testName=Mychangemeter Corrado Santagati&studentName=luigi.iuso",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(10)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=232&testName=Mychangemeter Corrado Santagati&studentName=katia.maltoni",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(10)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=230&testName=Mychangemeter Corrado Santagati&studentName=manuela.parravicini",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=116&idTrack=235&testName=test vito&studentName=alessandro",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(0)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=180&testName=Mychangemeter Corrado Santagati&studentName=luigi",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(8)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=233&testName=Mychangemeter Corrado Santagati&studentName=luigi.iuso",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(10)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=232&testName=Mychangemeter Corrado Santagati&studentName=katia.maltoni",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(10)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=230&testName=Mychangemeter Corrado Santagati&studentName=manuela.parravicini",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ]
        ],
        "total_result": "90"
      },
      {
        "id": "12287",
        "userid": "/csantagati",
        "firstname": "Corrado",
        "lastname": "Santagati",
        "email": "luigi",
        "register_date": "2016-09-08 09:36:53",
        "lastenter": "2016-10-28 19:01:28",
        "name": "Corrado Santagati",
        "activities_results": [
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "75",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "81",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(17)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "81",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(21)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [],
          [
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=116&idTrack=235&testName=test vito&studentName=alessandro",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(0)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=180&testName=Mychangemeter Corrado Santagati&studentName=luigi",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(8)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=233&testName=Mychangemeter Corrado Santagati&studentName=luigi.iuso",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(10)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=232&testName=Mychangemeter Corrado Santagati&studentName=katia.maltoni",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(10)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=230&testName=Mychangemeter Corrado Santagati&studentName=manuela.parravicini",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(20)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=211&testName=Mychangemeter Corrado Santagati&studentName=csantagati",
              "active": "true"
            }
          ],
          [
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=116&idTrack=235&testName=test vito&studentName=alessandro",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(0)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=180&testName=Mychangemeter Corrado Santagati&studentName=luigi",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(8)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=233&testName=Mychangemeter Corrado Santagati&studentName=luigi.iuso",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(10)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=232&testName=Mychangemeter Corrado Santagati&studentName=katia.maltoni",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(10)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=230&testName=Mychangemeter Corrado Santagati&studentName=manuela.parravicini",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(20)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=211&testName=Mychangemeter Corrado Santagati&studentName=csantagati",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ]
        ],
        "total_result": "90"
      },
      {
        "id": "12288",
        "userid": "/claudia.tartarotti",
        "firstname": "Claudia",
        "lastname": "Tartarotti",
        "email": "luigi",
        "register_date": "2016-09-08 09:43:37",
        "lastenter": "2016-10-28 18:31:06",
        "name": "Claudia Tartarotti",
        "activities_results": [
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "75",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "81",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(17)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "81",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(21)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [],
          [
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=116&idTrack=235&testName=test vito&studentName=alessandro",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(0)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=180&testName=Mychangemeter Corrado Santagati&studentName=luigi",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(8)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=233&testName=Mychangemeter Corrado Santagati&studentName=luigi.iuso",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(10)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=232&testName=Mychangemeter Corrado Santagati&studentName=katia.maltoni",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(10)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=230&testName=Mychangemeter Corrado Santagati&studentName=manuela.parravicini",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(20)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=211&testName=Mychangemeter Corrado Santagati&studentName=csantagati",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(9)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=231&testName=Mychangemeter Corrado Santagati&studentName=claudia.tartarotti",
              "active": "true"
            }
          ],
          [
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=116&idTrack=235&testName=test vito&studentName=alessandro",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(0)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=180&testName=Mychangemeter Corrado Santagati&studentName=luigi",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(8)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=233&testName=Mychangemeter Corrado Santagati&studentName=luigi.iuso",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(10)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=232&testName=Mychangemeter Corrado Santagati&studentName=katia.maltoni",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(10)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=230&testName=Mychangemeter Corrado Santagati&studentName=manuela.parravicini",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(20)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=211&testName=Mychangemeter Corrado Santagati&studentName=csantagati",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(9)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=231&testName=Mychangemeter Corrado Santagati&studentName=claudia.tartarotti",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ]
        ],
        "total_result": "90"
      },
      {
        "id": "12265",
        "userid": "/pepe.zamora",
        "firstname": "Pepe",
        "lastname": "Zamora",
        "email": "",
        "register_date": "2016-07-29 14:49:17",
        "lastenter": null,
        "name": "Pepe Zamora",
        "activities_results": [
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "75",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "81",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(17)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [
            {
              "icon": "bar-chart",
              "showIcon": "true",
              "value": "81",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "true"
            },
            {
              "icon": "bar-chart",
              "showIcon": "false",
              "value": "(21)",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [],
          [
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=116&idTrack=235&testName=test vito&studentName=alessandro",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(0)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=180&testName=Mychangemeter Corrado Santagati&studentName=luigi",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(8)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=233&testName=Mychangemeter Corrado Santagati&studentName=luigi.iuso",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(10)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=232&testName=Mychangemeter Corrado Santagati&studentName=katia.maltoni",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(10)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=230&testName=Mychangemeter Corrado Santagati&studentName=manuela.parravicini",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(20)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=211&testName=Mychangemeter Corrado Santagati&studentName=csantagati",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(9)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=231&testName=Mychangemeter Corrado Santagati&studentName=claudia.tartarotti",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ],
          [
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(1)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=116&idTrack=235&testName=test vito&studentName=alessandro",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(0)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=180&testName=Mychangemeter Corrado Santagati&studentName=luigi",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(8)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=233&testName=Mychangemeter Corrado Santagati&studentName=luigi.iuso",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(10)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=232&testName=Mychangemeter Corrado Santagati&studentName=katia.maltoni",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(10)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=230&testName=Mychangemeter Corrado Santagati&studentName=manuela.parravicini",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=116&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(20)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=211&testName=Mychangemeter Corrado Santagati&studentName=csantagati",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "ico-wt-sprite",
              "showIcon": "true",
              "value": "",
              "showValue": "false",
              "link": "index.php?r=test360/report&idTest=92&showAuto=1&showEtero=1",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "(9)",
              "showValue": "true",
              "link": "index.php?modname=coursereport&op=testreport&idTest=92&idTrack=231&testName=Mychangemeter Corrado Santagati&studentName=claudia.tartarotti",
              "active": "true"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            },
            {
              "icon": "",
              "showIcon": "false",
              "value": "-",
              "showValue": "true",
              "link": "javascript:void(0)",
              "active": "false"
            }
          ]
        ],
        "total_result": "90"
      }
    ]
  };


  var loadUserData = function () {

    $.ajax({

      type: 'post',
      url: '/appLms/index.php?r=lms/coursereport/getDetailCourseReport',
      data: _data,
      beforeSend: function () {
        $('.loading').html(_loadingSnippet);
      },
      success: function(data) {
        $('.loading').html(data);
        return data;
      },
      error: function(e) { //in sostituzione dei servizi mancanti uso fakedata - PRIMA CHIAMATA AJAX
        $('.loading').html('errore: ' + e.message);
        return false;
      }
    });
  };

  /**
   * Funzione per popolare select dei filtri
   */
  var fillActivitiesFilter = function () {
    var activities = userData.activities;
    var $filter;
    var _selected;
    var _option;

    for (var i = 0; i < 4; i++) {
      $filter = $($('.js-test-filter')[i]);
      $.each(activities, function (j, elem) {
        _selected = (j === i) ? ' selected' : '';
        _option = '<option value="' + elem.id +'"' + _selected +'>' + elem.name + '</option>';
        $filter.append(_option);
      });
    }
  };

  /**
   * Funzione per parsare il risultato del singolo test
   * @param result
   */
  var parseResult = function (result) {

    var _parsed = [];

    $.each(result, function (i, elem) {
      _parsed.push(elem.value);
    });

    return _parsed.join(' ');
  };

  /**
   * Funzione per popolare la riga del singolo studente
   * @param   {object}   student   -   oggetto con i dati relativi allo studente
   */
  var buildStudentRow = function (student) {
    var _student = '<tr class="student" data-student="' + student.id +'">';
    _student += '<td class="student__name">' + student.firstname + ' ' + student.lastname + '</td>';
    _student += '<td class="student__info">' + student.email + '</td>';


    for (var i = 0; i < 4; i++) {
      _student += '<td class="student__test-result student__test-result--' + i + '">' + parseResult(student.activities_results[i]) + '</td>';
    }

    _student += '<td class="student__total-result">' + student.total_result + '</td>';
    _student += '</tr>';

    return _student;
  };

  var fillTable = function () {
    var students = userData.students;


    $.each(students, function(i, elem) {
      $table.append(buildStudentRow(elem));
    });

  };

  /**
   * Funzione per aggiornare le info dell'utente in base la filtro
   * @param    {string}   info   -   valore del filtro info utente
   */
  var updateUsersInfo = function (info) {
    var _students = userData.students;
    var _student;

    $.each($table.children('.student'), function (i, elem) {
      _student = _students[i];
      $(elem).children('.student__info').html(_student[info]);
    });
  };

  $(document).ready(function() {

    $table = $('.js-details-table');

    $('.js-details').on('click', function () {
      fillActivitiesFilter();
      fillTable();
//      userData = loadUserData();
      console.log('loadUserData -> ' + loadUserData());
    });

    $('.js-user-detail-filter').on('change', function () {
      var _info = $(this).val();

      updateUsersInfo(_info);
    });
  });

})(jQuery);


