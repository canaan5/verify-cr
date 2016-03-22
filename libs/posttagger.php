<?php
class PosTagger {
        private $dict;

        public function __construct($lexicon) {
                $fh = fopen($lexicon, 'r');
                while($line = fgets($fh)) {
                        $tags = explode(' ', $line);
                        $this->dict[strtolower(array_shift($tags))] = $tags;
                }
                fclose($fh);
        }

        public function tag($text) {
                preg_match_all("/[\w\d\.]+/", $text, $matches);
                $nouns = array('NN', 'NNS');

                $return = array();
                $i = 0;
                foreach($matches[0] as $token) {
                        // default to a common noun
                        $return[$i] = array('token' => $token, 'tag' => 'NN');

                        // Check if word is a single later, if it is, exclude it
                        if ( strlen($token) <= 1 )
                        {
                                echo $token;
                                $return[$i]['token'] = str_replace($token, '', $token);
                        }

//                        var_dump($return[$i]['token']);
//                        die;


                        // remove trailing full stops
                        if(substr($token, -1) == '.') {
                                $token = preg_replace('/\.+$/', '', $token);
                        }

                        // get from dict if set
                        if(isset($this->dict[strtolower($token)])) {
                                $return[$i]['tag'] = $this->dict[strtolower($token)][0];
                        }

                        // Converts verbs after 'the' to nouns
                        if($i > 0) {
                                if($return[$i - 1]['tag'] == 'DT' &&
                                        in_array($return[$i]['tag'],
                                                        array('VBD', 'VBP', 'VB'))) {
                                        $return[$i]['tag'] = 'NN';
                                }
                        }

                        // Convert noun to number if . appears
                        if($return[$i]['tag'][0] == 'N' && strpos($token, '.') !== false) {
                                $return[$i]['tag'] = 'CD';
                        }

                        // Convert noun to past particile if ends with 'ed'
                        if($return[$i]['tag'][0] == 'N' && substr($token, -2) == 'ed') {
                                $return[$i]['tag'] = 'VBN';
                        }

                        // Anything that ends 'ly' is an adverb
                        if(substr($token, -2) == 'ly') {
                                $return[$i]['tag'] = 'RB';
                        }

                        // Common noun to adjective if it ends with al
                        if(in_array($return[$i]['tag'], $nouns)
                                                && substr($token, -2) == 'al') {
                                $return[$i]['tag'] = 'JJ';
                        }

                        // Noun to verb if the word before is 'would'
                        if($i > 0) {
                                if($return[$i]['tag'] == 'NN'
                                        && strtolower($return[$i-1]['token']) == 'would') {
                                        $return[$i]['tag'] = 'VB';
                                }
                        }
                        if(substr($token, -3) == 'ies') {
                                $return[$i]['tag'] = 'NNS';
                                $return[++$i]=array('token'=>substr($token,0,(strlen($token))-3).'y','tag'=>'NN');
                        }
                        if(substr($token, -2) == 'ed') {
                                $return[$i]['tag'] = 'NNS';
                                $return[++$i]=array('token'=>substr($token,0,(strlen($token))-1),'tag'=>'NN');
                                $return[++$i]=array('token'=>substr($token,0,(strlen($token))-1).'s','tag'=>'NN');
                                $return[++$i]=array('token'=>substr($token,0,(strlen($token))-2).'ing','tag'=>'NN');
                        }
                        if(substr($token, -2) == 'er') {
                                $return[$i]['tag'] = 'NNS';
                                $return[++$i]=array('token'=>substr($token,0,(strlen($token))-1),'tag'=>'NN');
                                $return[++$i]=array('token'=>substr($token,0,(strlen($token))).'s','tag'=>'NN');
                                $return[++$i]=array('token'=>substr($token,0,(strlen($token))-2).'ing','tag'=>'NN');
                        }
                        if(substr($token, -3) == 'ing') {
                                $return[$i]['tag'] = 'NNS';
                                $return[++$i]=array('token'=>substr($token,0,(strlen($token))-3),'tag'=>'NN');
                                $return[++$i]=array('token'=>substr($token,0,(strlen($token))-3).'s','tag'=>'NN');
                        }
                        // Convert noun to plural if it ends with an s
                        if(substr($token, -1) == 's') {
                                $return[$i]['tag'] = 'NNS';
                                $return[++$i]=array('token'=>substr($token,0,(strlen($token))-1),'tag'=>'NN');
                        }

                        if($return[$i]['tag'] == 'NN' && substr($token, -1) != 's') {
                                $return[$i]['tag'] = 'NNS';
                                $return[++$i]=array('token'=>substr($token,0,(strlen($token))).'s','tag'=>'NN');
                        }


                        if(substr($token, -1) == 'y') {
                                $return[$i]['tag'] = 'NN';
                                $return[++$i]=array('token'=>substr($token,0,(strlen($token))-1).'ies','tag'=>'NN');
                        }



                        // Convert common noun to gerund
                        if(in_array($return[$i]['tag'], $nouns)
                                        && substr($token, -3) == 'ing') {
                                $return[$i]['tag'] = 'VBG';
                        }

                        // If we get noun noun, and the second can be a verb, convert to verb
                        if($i > 0) {
                                if(in_array($return[$i]['tag'], $nouns)
                                                && in_array($return[$i-1]['tag'], $nouns)
                                                && isset($this->dict[strtolower($token)])) {
                                        if(in_array('VBN', $this->dict[strtolower($token)])) {
                                                $return[$i]['tag'] = 'VBN';
                                        } else if(in_array('VBZ',
                                                        $this->dict[strtolower($token)])) {
                                                $return[$i]['tag'] = 'VBZ';
                                        }
                                }
                        }

                        $i++;
                }

                return $return;
        }
}
?>