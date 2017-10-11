<?php // $Id: format.php,v 1.15.2.11 2007/11/29 04:30:28 toyomoyo Exp $
//
///////////////////////////////////////////////////////////////
// The GIFT import filter was designed as an easy to use method 
// for teachers writing questions as a text file. It supports most
// question types and the missing word format.
//
// Multiple Choice / Missing Word
//     Who's buried in Grant's tomb?{~Grant ~Jefferson =no one}
//     Grant is {~buried =entombed ~living} in Grant's tomb.
// True-False:
//     Grant is buried in Grant's tomb.{FALSE}
// Short-Answer.
//     Who's buried in Grant's tomb?{=no one =nobody}
// Numerical
//     When was Ulysses S. Grant born?{#1822:5}
// Matching
//     Match the following countries with their corresponding
//     capitals.{=Canada->Ottawa =Italy->Rome =Japan->Tokyo}
//
// Comment lines start with a double backslash (//). 
// Optional question names are enclosed in double colon(::). 
// Answer feedback is indicated with hash mark (#).
// Percentage answer weights immediately follow the tilde (for
// multiple choice) or equal sign (for short answer and numerical),
// and are enclosed in percent signs (% %). See docs and examples.txt for more.
// 
// This filter was written through the collaboration of numerous 
// members of the Moodle community. It was originally based on 
// the missingword format, which included code from Thomas Robb
// and others. Paul Tsuchido Shew wrote this filter in December 2003.
//////////////////////////////////////////////////////////////////////////
// Based on default.php, included by ../import.php

class qformat_gift {

    function provide_import() {
        return true;
    }

    function provide_export() {
        return true;
    }

    function answerweightparser(&$answer) {
        $answer = substr($answer, 1);                        // removes initial %
        $end_position  = strpos($answer, "%");
        $answer_weight = substr($answer, 0, $end_position);  // gets weight as integer
        $answer_weight = $answer_weight/100;                 // converts to percent
        $answer = substr($answer, $end_position+1);          // removes comment from answer
        return $answer_weight;
    }


    function commentparser(&$answer) {
        if (strpos($answer,"#") > 0){
            $hashpos = strpos($answer,"#");
            $comment = substr($answer, $hashpos+1);
            $comment = addslashes(trim($this->escapedchar_post($comment)));
            $answer  = substr($answer, 0, $hashpos);
        } else {
            $comment = " ";
        }
        return $comment;
    }

    function split_truefalse_comment($comment){
        // splits up comment around # marks
        // returns an array of true/false feedback
        $bits = explode('#',$comment);
        $feedback = array('wrong' => $bits[0]);
        if (count($bits) >= 2) {
            $feedback['right'] = $bits[1];
        } else {
            $feedback['right'] = '';
        }
        return $feedback;
    }
    
    function escapedchar_pre($string) {
        //Replaces escaped control characters with a placeholder BEFORE processing
        
        $escapedcharacters = array("\\:",    "\\#",    "\\=",    "\\{",    "\\}",    "\\~",    "\\n"   );  //dlnsk
        $placeholders      = array("&&058;", "&&035;", "&&061;", "&&123;", "&&125;", "&&126;", "&&010" );  //dlnsk

        $string = str_replace("\\\\", "&&092;", $string);
        $string = str_replace($escapedcharacters, $placeholders, $string);
        $string = str_replace("&&092;", "\\", $string);
        return $string;
    }

    function escapedchar_post($string) {
        //Replaces placeholders with corresponding character AFTER processing is done
        $placeholders = array("&&058;", "&&035;", "&&061;", "&&123;", "&&125;", "&&126;", "&&010"); //dlnsk
        $characters   = array(":",     "#",      "=",      "{",      "}",      "~",      "\n"   ); //dlnsk
        $string = str_replace($placeholders, $characters, $string);
        return $string;
    }

    function check_answer_count( $min, $answers, $text ) {
        $countanswers = count($answers);
        if ($countanswers < $min) {
            //$importminerror = get_string( 'importminerror', 'quiz' );
            //$this->error( $importminerror, $text );
            return false;
        }

        return true;
    }
    function defaultquestion() {
        
        require_once($GLOBALS['where_lms'].'/modules/question/class.question.php');
        $question = new QuestionRaw();
		
        return $question;
    }
	
	function readquestions($lines) {
     
        $questions = array();
        $currentquestion = array();

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                if (!empty($currentquestion)) {
                    if ($question = $this->readquestion($currentquestion)) {
                        $questions[] = $question;
                    }
                    $currentquestion = array();
                }
            } else {
                $currentquestion[] = $line;
            }
        }

        if (!empty($currentquestion)) {  // There may be a final question
            if ($question = $this->readquestion($currentquestion)) {
                $questions[] = $question;
            }
        }

        return $questions;
    }
   
    function readquestion($lines) {
    	// Given an array of lines known to define a question in this format, this function
    	// converts it into a question object suitable for processing and insertion.

        $question = $this->defaultquestion();
        $comment = NULL;
        
        // define replaced by simple assignment, stop redefine notices
        $gift_answerweight_regex = "^%\-*([0-9]{1,2})\.?([0-9]*)%";        

        // REMOVED COMMENTED LINES and IMPLODE
        foreach ($lines as $key => $line) {
            $line = trim($line);
            if (substr($line, 0, 2) == "//") {
                $lines[$key] = " ";
            }
        }

        $text = trim(implode(" ", $lines));

        if ($text == "") {
            return false;
        }

        // Substitute escaped control characters with placeholders
        $text = $this->escapedchar_pre($text);

        // Look for category modifier ---------------------------------------------------------
        if (preg_match( '/^\$CATEGORY:/', $text)) {
            // $newcategory = $matches[1];
            $newcategory = trim(substr( $text, 10 ));
			
			if ( strpos($newcategory, '$CUSTOMFIELD:') === false ) {
				$newcategory = trim(substr( $newcategory, 0,  strpos($newcategory, "::")));
			} else {
				$newcategory = trim(substr( $newcategory, 0,  strpos($newcategory, '$CUSTOMFIELD:')));
			}
			
            $question->setCategoryFromName($newcategory);
            $text = trim(substr($text, 10+strlen($newcategory)));
                        
            // build fake question to contain category
            
          	// XXX: create a category !
            //return true;
        }
        
        // Look for customfield modifier ---------------------------------------------------------
        require_once(_adm_.'/lib/lib.customfield.php');
        $fman = new CustomFieldList();
        $fman->setFieldArea( "LO_TEST" );
        $arrCustomField = $fman->playFieldsFlat();
        

        foreach ($arrCustomField as $field) { 

            // $field['code']
            // $field['id']
            if (preg_match( '/^\$CUSTOMFIELD:'.$field['code'].':/', $text)) {
                $newcf = trim(substr( $text, strlen($field['code'])+strlen('\$CUSTOMFIELD:') ));
                if ( strpos($newcf, '$CUSTOMFIELD:') === false ) {
                    $newcf = trim(substr( $newcf, 0,  strpos($newcf, '::')));
                } else {
                    $newcf = trim(substr( $newcf, 0,  strpos($newcf, '$CUSTOMFIELD:')));
                }
                
                $found = false;
                
                foreach ($field['code_value'] as $key=>$value) {
                        //$field['code'].'_'.$key.'">'.$value.':</label> '
                    if ($value == $newcf) {
                        // setto l'array cf
                        $arrValueCustomField [] = array("idField" => $field['id'], "nameField" => $field['code'], "idSon" => $key, "nameSon" => $value);
                        $found = true;
                    }
                }
                if ($found == false) {
                    // se non lo ha trovato metto valore zero
                    $arrValueCustomField [] = array("idField" => $field['id'], "nameField" => $field['code'], "idSon" => 0, "nameSon" => "");
                }
                // tolgo dalla stringa il customfield trattato
                $text = trim(substr($text, strlen($field['code'])+strlen('\$CUSTOMFIELD:')+strlen($newcf)));
            }
        }
        $question->customfield = $arrValueCustomField;
        
        // QUESTION NAME parser --------------------------------------------------------------
        if (substr($text, 0, 2) == "::") {
            $text = substr($text, 2);

            $namefinish = strpos($text, "::");
            if ($namefinish === false) {
                $question->prompt = false;
                // name will be assigned after processing question text below
            } else {
                $questionname = substr($text, 0, $namefinish);
                $question->prompt = addslashes(trim($this->escapedchar_post($questionname)));
                $text = trim(substr($text, $namefinish+2)); // Remove name from text
            }
        } else {
            $question->prompt = false;
        }


        // FIND ANSWER section -----------------------------------------------------------------
        // no answer means its a description
        $answerstart = strpos($text, "{");
        $answerfinish = strpos($text, "}");

        $description = false;
        if (($answerstart === false) and ($answerfinish === false)) {
            $description = true;
            $answertext = '';
            $answerlength = 0;
        }
        elseif (!(($answerstart !== false) and ($answerfinish !== false))) {
            //$this->error( get_string( 'braceerror', 'quiz' ), $text );
            return false;
        }
        else {
            $answerlength = $answerfinish - $answerstart;
            $answertext = trim(substr($text, $answerstart + 1, $answerlength - 1));
        }

      
		// Format QUESTION TEXT without answer, inserting "_____" as necessary
        if ($description) {
            $text = $text;
        }
        elseif (substr($text, -1) == "}") {
            // no blank line if answers follow question, outside of closing punctuation
            $text = substr_replace($text, "", $answerstart, $answerlength+1);
        } else {
            // inserts blank line for missing word format
            $text = substr_replace($text, "_____", $answerstart, $answerlength+1);
        }

        // get text format from text
        $oldtext = $text;
        $textformat = 0;
        if (substr($text,0,1)=='[') {
            $text = substr( $text,1 );
            $rh_brace = strpos( $text, ']' );
            $qtformat= substr( $text, 0, $rh_brace );
            $text = substr( $text, $rh_brace+1 );
                     
        }
        // i must find out for what this param is used
        $question->textformat = $textformat;
 		
 		// question text 
        $question->quest_text  = addslashes(trim($this->escapedchar_post($text)));

        // set question name if not already set
		if ($question->prompt === false) {
			$question->prompt = $question->quest_text;
		}

        // ensure name is not longer than 250 characters
        $question->prompt = $question->prompt ;
        $question->prompt = strip_tags(substr( $question->prompt, 0, 250 ));

        // determine QUESTION TYPE -------------------------------------------------------------
        $question->qtype = NULL;

        // give plugins first try
        // plugins must promise not to intercept standard qtypes
        // MDL-12346, this could be called from lesson mod which has its own base class =(
        /*
        if (method_exists($this, 'try_importing_using_qtypes') && ($try_question = $this->try_importing_using_qtypes( $lines, $question, $answertext ))) {
            return $try_question;
        }
		*/
        if ($description) {
            $question->qtype = 'title';
        }
        elseif ($answertext == '') {
            $question->qtype = 'extended_text';
        }
        elseif ($answertext{0} == "#"){
            $question->qtype = 'numerical';

		} elseif (strpos($answertext, "~") !== false)  {
			
			// only Multiplechoice questions contain tilde ~
			if (strpos($answertext,"=") === false) {
				
				// multiple answers are enabled if no single answer is 100% correct
				$question->qtype = 'choice_multiple';                      
			} else {
				
				// only one answer allowed (the default)
				$question->qtype = 'choice';
			}
        } elseif (strpos($answertext, "=")  !== false 
                && strpos($answertext, "->") !== false) {
            // only Matching contains both = and ->
            $question->qtype = 'associate';

        } else { // either TRUEFALSE or SHORTANSWER
    
            // TRUEFALSE question check
            
            $truefalse_check = $answertext;
            if (strpos($answertext,"#") > 0){ 
            
                // strip comments to check for TrueFalse question
                $truefalse_check = trim(substr($answertext, 0, strpos($answertext,"#")));
            }
            $valid_tf_answers = array("T", "TRUE", "F", "FALSE");
            if(in_array($truefalse_check, $valid_tf_answers)) {
				
				$question->qtype = 'truefalse';
            } else { // Must be SHORTANSWER
            	
				$question->qtype = 'shortanswer';
            }
        }

        if (!isset($question->qtype)) { return false; }

        switch ($question->qtype) {
        	case 'extended_text' : 
            case 'title' : {
				
				return $question;
            };break;
            case 'choice' :
            case 'choice_multiple' : {
                
                $answertext = str_replace("=", "~=", $answertext);
                $answers = explode("~", $answertext);
                if (isset($answers[0])) {
                    $answers[0] = trim($answers[0]);
                }
                if (empty($answers[0])) {
                    array_shift($answers);
                }
                $countanswers = count($answers);
                
                if (!$this->check_answer_count(2, $answers, $text)) {
                    return false;
                }
				$num_correct = 0;
                foreach ($answers as $key => $answer) {
                    $answer = trim($answer);
					
					$oAnswer = new AnswerRaw();
					
                    // determine answer weight
                    if ($answer[0] == "=") {
                        $answer = substr($answer, 1);
                        if(preg_match($gift_answerweight_regex, $answer)) $answer_weight = $this->answerweightparser($answer);
                        else $answer_weight = 1;
                        
                    } elseif(preg_match($gift_answerweight_regex, $answer)) {    // check for properly formatted answer weight
                        $answer_weight = $this->answerweightparser($answer);
                    
                    } else {     //default, i.e., wrong anwer
                        $answer_weight = 0;
                    }
                    $oAnswer->is_correct 	= ($answer_weight > 0 ? 1 : 0 );
					if($oAnswer->is_correct) $num_correct++;
                    if($answer_weight >= 0) $oAnswer->score_correct = $answer_weight;
                    else $oAnswer->score_penalty = (-1)*$answer_weight;
                    $oAnswer->comment 		= $this->commentparser($answer); // commentparser also removes comment from $answer
                    $oAnswer->text 			= addslashes($this->escapedchar_post($answer));
                    
                    $question->answers[] 	= $oAnswer;
                    
                }  // end foreach answer
    			if($num_correct > 1) $question->qtype = 'choice_multiple';
						
                return $question;
            };break;
            case 'associate' : {
                $answers = explode("=", $answertext);
                if (isset($answers[0])) {
                    $answers[0] = trim($answers[0]);
                }
                if (empty($answers[0])) {
                    array_shift($answers);
                }
    
                if (!$this->check_answer_count( 2,$answers,$text )) {
                    return false;
                }
    
                foreach ($answers as $key => $answer) {
                    $answer = trim($answer);
                    if (strpos($answer, "->") === false) {
                    	
                        return false;
                    }

					$oAnswer = new AnswerRaw();
					$oExtra = new AnswerRaw();
					
                    $marker = strpos($answer,"->");
					$oAnswer->text = addslashes(trim($this->escapedchar_post(substr($answer, 0, $marker))));
					$oExtra->text = addslashes(trim($this->escapedchar_post(substr($answer, $marker+2))));
					
					$question->answers[] 	= $oAnswer;
					$question->extra_info[] = $oExtra;
                }  // end foreach answer
    
                return $question;
            };break;
			case 'truefalse' : {
				
				$answer = $answertext;
				$comment = $this->commentparser($answer); // commentparser also removes comment from $answer
				$feedback = $this->split_truefalse_comment($comment);
				
				$true_answer = new AnswerRaw();
				$true_answer->text = Lang::t('_TRUE', 'test');
				$false_answer = new AnswerRaw();
				$false_answer->text = Lang::t('_FALSE', 'test');
				
				if ($answer == "T" OR $answer == "TRUE") {
					$true_answer->is_correct = 1;
					$true_answer->score_correct = 1;
				} else {
					$false_answer->is_correct = 1;
					$false_answer->score_correct = 1;
				}
				
				$true_answer->comment = $feedback['right'];
				$false_answer->comment = $feedback['wrong'];
				    
				$question->answers[] 	= $true_answer;
				$question->answers[] 	= $false_answer;
                
                // change because now we emulate this type of question
                $question->qtype = 'choice';
                
                return $question;
            };break;
            case 'shortanswer' : {
				
				$answers = explode("~", $answertext);
				if (isset($answers[0])) {
					$answers[0] = trim($answers[0]);
				}
				if (empty($answers[0])) {
					array_shift($answers);
				}
				$countanswers = count($answers);
				    
				if (!$this->check_answer_count(2, $answers, $text)) {
					return false;
				}
				
				foreach ($answers as $key => $answer) {
					
					$answer = trim($answer);

					$oAnswer = new AnswerRaw();
					// Answer Weight
					if (preg_match($gift_answerweight_regex, $answer)) {    // check for properly formatted answer weight
					
						$answer_weight = $this->answerweightparser($answer);
					} else {     //default, i.e., full-credit anwer
					
						$answer_weight = 1;
					}					
					$oAnswer->is_correct 	= ($answer_weight > 0 ? 1 : 0 );
					
					if($answer_weight >= 0) $oAnswer->score_correct = $answer_weight;
					else $oAnswer->score_penalty = (-1)*$answer_weight;
					$oAnswer->comment 		= $this->commentparser($answer); // commentparser also removes comment from $answer
					$oAnswer->text 			= addslashes($this->escapedchar_post($answer));
					    
					$question->answers[] 	= $oAnswer;
					
				}  // end foreach answer
				
                // change because now we emulate this type of question
                $question->qtype = 'choice';
                
                return $question;
            };break;
            case 'numerical' : {
                // Note similarities to ShortAnswer
                $answertext = substr($answertext, 1); // remove leading "#"

                // If there is feedback for a wrong answer, store it for now.
                if (($pos = strpos($answertext, '~')) !== false) {
                    $wrongfeedback = substr($answertext, $pos);
                    $answertext = substr($answertext, 0, $pos);
                } else {
                    $wrongfeedback = '';
                }

                $answers = explode("=", $answertext);
                if (isset($answers[0])) {
                    $answers[0] = trim($answers[0]);
                }
                if (empty($answers[0])) {
                    array_shift($answers);
                }
    
                if (count($answers) == 0) {
                    // invalid question
                    return false;
                }

                foreach ($answers as $key => $answer) {
                    $answer = trim($answer);

					$oAnswer = new AnswerRaw();
					
                    // Answer weight
                    if (preg_match($gift_answerweight_regex, $answer)) {
						// check for properly formatted answer weight
                        $answer_weight = $this->answerweightparser($answer);
                    } else {
                    	//default, i.e., full-credit anwer
                        $answer_weight = 1;
                    }
                    
                    $oAnswer->score_correct = $answer_weight;
                    $oAnswer->comment = $this->commentparser($answer); //commentparser also removes comment from $answer

                    //Calculate Answer and Min/Max values
                    if (strpos($answer,"..") > 0) { // optional [min]..[max] format
                        $marker = strpos($answer,"..");
                        $max = trim(substr($answer, $marker+2));
                        $min = trim(substr($answer, 0, $marker));
                        $ans = ($max + $min)/2;
                        $tol = $max - $ans;
                    } elseif (strpos($answer,":") > 0){ // standard [answer]:[errormargin] format
                        $marker = strpos($answer,":");
                        $tol = trim(substr($answer, $marker+1));
                        $ans = trim(substr($answer, 0, $marker));
                    } else { // only one valid answer (zero errormargin)
                        $tol = 0;
                        $ans = trim($answer);
                    }
    
                    if (!(is_numeric($ans) || $ans = '*') || !is_numeric($tol)) {
                        
                        return false;
                    }
                    
                    // store results
                    $oAnswer->text = $ans;
                    $oAnswer->tolerance = $tol;
                    
                    $question->answers[] = $oAnswer;
                } // end foreach

				/*
                if ($wrongfeedback) {
					$oAnswer = new AnswerRaw();
                    $oAnswer->score_correct = 0;
                    $oAnswer->comment = $this->commentparser($wrongfeedback);
                    $oAnswer->text = '';
                    $oAnswer->tolerance = '';
                    $question->answers[] = $oAnswer;
                }*/

                return $question;
            };break;
			default: {
				//$giftnovalidquestion = get_string('giftnovalidquestion','quiz');
				//$this->error( $giftnovalidquestion, $text );
				return false;
			};break;                
        
        } // end switch ($question->qtype)

    }    // end function readquestion($lines)
	
	function repchar( $text, $format=0 ) {
	    // escapes 'reserved' characters # = ~ { ) : and removes new lines
	    // also pushes text through format routine
	    $reserved = array( '#', '=', '~', '{', '}', ':', "\n","\r");
	    $escaped =  array( '\#','\=','\~','\{','\}','\:','\n',''  ); //dlnsk
	
	    $newtext = str_replace( $reserved, $escaped, $text ); 
	    $format = 0; // turn this off for now
	    if ($format) {
	        //$newtext = format_text( $format );
	    }
	    return $newtext;
	}
	
	function writequestion( $question ) {
	    // turns question into string
	    // question reflects database fields for general question and specific to type
	
	    // initial string;
	    $expout = "";
	
	    // add comment
	    $expout .= "// question: $question->id  \n";

	    if ($question->id_category != ""){
	        $expout .= "\$CATEGORY:$question->id_category\n";
	    }
		
            // Customfield
            if (is_array ($question->customfield)){
                foreach ($question->customfield as $field) { 
                    $expout .= "\$CUSTOMFIELD:".$field['code'].":".$field['code_value']."\n";
                }
	    }
            
	    // get  question text format
	    /*$textformat = $question->textformat;
	    $question->text = "";
	    if ($textformat!=FORMAT_MOODLE) {
	        $question->text = text_format_name( (int)$textformat );
	        $question->text = "[$question->text]";
	    }*/
	    $qtext_format = "[".$question->qtype."]";
	    // output depends on question type
	    switch($question->qtype) {
			case 'category' : {
				// not a real question, used to insert category switch
				$expout .= "\$CATEGORY: $question->category\n";    
			};break;
			case 'title' : {
				if($question->prompt != '') $expout .= '::'.$this->repchar($question->prompt).'::';
				$expout .= $qtext_format;
				$expout .= $this->repchar( $question->quest_text);
			};break;
		    case 'extended_text' : {
		        $expout .= '::'.$this->repchar($question->prompt).'::';
		        $expout .= $qtext_format;
		        $expout .= $this->repchar( $question->quest_text);
		        $expout .= "{}\n";
		    };break;
		    case 'truefalse' : {/*
		        $trueanswer = $question->options->answers[$question->options->trueanswer];
		        $falseanswer = $question->options->answers[$question->options->falseanswer];
		        if ($trueanswer->fraction == 1) {
		            $answertext = 'TRUE';
		            $right_feedback = $trueanswer->feedback;
		            $wrong_feedback = $falseanswer->feedback;
		        } else {
		            $answertext = 'FALSE';
		            $right_feedback = $falseanswer->feedback;
		            $wrong_feedback = $trueanswer->feedback;
		        }
		
		        $wrong_feedback = $this->repchar($wrong_feedback);
		        $right_feedback = $this->repchar($right_feedback);
		        $expout .= "::".$this->repchar($question->prompt)."::".$qtext_format.$this->repchar( $question->quest_text )."{".$this->repchar( $answertext );
		        if ($wrong_feedback) {
		            $expout .= "#" . $wrong_feedback;
		        } else if ($right_feedback) {
		            $expout .= "#";
		        }
		        if ($right_feedback) {
		            $expout .= "#" . $right_feedback;
		        }
		        $expout .= "}\n";*/
		    }//;break;
		    
		    case 'shortanswer' : {/*
		        $expout .= "::".$this->repchar($question->prompt)."::".$qtext_format.$this->repchar( $question->quest_text )."{\n";
		        foreach($question->options->answers as $answer) {
		            $weight = 100 * $answer->score_correct;
		            $expout .= "\t=%".$weight."%".$this->repchar( $answer->text )."#".$this->repchar( $answer->comment )."\n";
		        }
		        $expout .= "}\n";*/
		    }//;break;
			case 'choice' :  {
				$expout .= "::".$this->repchar($question->prompt)."::".$qtext_format.$this->repchar( $question->quest_text )."{\n";
				
				foreach($question->answers as $answer) {
					if (($answer->score_correct == 1) || ($answer->score_correct == 0 && $answer->is_correct == 1) ) {
						$answertext = '=';
					}
					elseif ($answer->score_correct > 1) {
						$export_weight = $answer->score_correct*100;
						$answertext = "=%$export_weight%";
					}
					elseif ($answer->score_correct==0) {
						$answertext = '~';
					}
					else {
						$export_weight = $answer->score_correct*100;
						$answertext = "~%$export_weight%";
					}
					$expout .= "\t".$answertext.$this->repchar( $answer->text );
					if ($answer->comment!="") {
						$expout .= "#".$this->repchar( $answer->comment );
					}
					$expout .= "\n";
				}
				$expout .= "}\n";
			};break;
			case 'choice_multiple' : {
				$expout .= "::".$this->repchar($question->prompt)."::".$qtext_format.$this->repchar( $question->quest_text )."{\n";
				
				foreach($question->answers as $answer) {
					if ($answer->score_correct==1) {
						$answertext = '=';
					}
					elseif ($answer->score_correct==0) {
						$answertext = '~';
					}
					else {
						$export_weight = $answer->score_correct*100;
						$answertext = "~%$export_weight%";
					}
					$expout .= "\t".$answertext.$this->repchar( $answer->text );
					if ($answer->comment!="") {
						$expout .= "#".$this->repchar( $answer->comment );
					}
					$expout .= "\n";
				}
				$expout .= "}\n";
			};break;
		    case 'associate' : {
				$expout .= "::".$this->repchar($question->prompt)."::".$qtext_format.$this->repchar( $question->quest_text )."{\n";
				foreach($question->answers as $i => $subquestion) {
					$expout .= "\t=".$this->repchar( $subquestion->text )." -> ".$this->repchar( $question->extra_info[$i]->text )."\n";
				}
				$expout .= "}\n";
		    };break;
		    case NUMERICAL:
		        $expout .= "::".$this->repchar($question->prompt)."::".$qtext_format.$this->repchar( $question->quest_text )."{#\n";
		        foreach ($question->options->answers as $answer) {
		            if ($answer->text != '') {
		                $percentage = '';
		                if ($answer->score_correct < 1) {
		                    $pval = $answer->score_correct * 100;
		                    $percentage = "%$pval%";
		                }
		                $expout .= "\t=$percentage".$answer->text.":".(float)$answer->tolerance."#".$this->repchar( $answer->comment )."\n";
		            } else {
		                $expout .= "\t~#".$this->repchar( $answer->comment )."\n";
		            }
		        }
		        $expout .= "}\n";
		        break;
		    case DESCRIPTION:
		        $expout .= "// DESCRIPTION type is not supported\n";
		        break;
		    case MULTIANSWER:
		        $expout .= "// CLOZE type is not supported\n";
		        break;
		    default:
		        
				return false;
		}
	    // add empty line to delimit questions
	    $expout .= "\n";
	    return $expout;
	}
	
}

?>