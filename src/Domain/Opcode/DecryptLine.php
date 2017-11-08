<?php
namespace Zodream\Module\Gzo\Domain\Opcode;


class DecryptLine {

    /**
     * @var Line[]
     */
    protected $lines;

    /**
     * @var DecryptBlock
     */
    protected $block;

    protected $content = '';

    /**
     * @var int 当前id
     */
    protected $i = 0;

    public function __construct(array $lines, DecryptBlock $block) {
        $this->setLines($lines);
        $this->setBlock($block);
    }

    /**
     * @param mixed $lines
     */
    public function setLines(array $lines) {
        $this->lines = $lines;
    }

    /**
     * @param mixed $block
     */
    public function setBlock(DecryptBlock $block) {
        $this->block = $block;
    }

    public function decode() {
        $this->i = -1;
        while ($this->i < $this->count() - 1) {
            $this->i ++;
            $line = $this->lines[$this->i];
            $this->deLine($line, $this->i);
        }
        return $this->content;
    }

    public function deLine(Line $line, $i) {
        $method = 'de'.$line->op;
        $this->{$method}($line, $i);
        $this->setValue($line);
    }

    public function count() {
        return count($this->lines);
    }

    public function next() {
        $i = $this->i + 1;
        if ($this->count() > $i) {
            return $this->lines[$i];
        }
        return false;
    }

    public function last() {
        return $this->lines[$this->count() - 1];
    }

    public function map(callable $callback, $ops = null) {
        $i = 0;
        if (is_integer($ops)) {
            $i = $ops;
        }
        for (; $i < $this->count(); $i ++) {
            $line = $this->lines[$i];
            if (is_null($ops)
                || (is_string($ops) && $line->op == $ops)
                || (is_array($ops) && in_array($line->op, $ops))
                || (is_callable($ops) && $ops($line))) {
                if (false === $callback($line, $i)) {
                    break;
                }
            }
        }
    }

    public function setValue(Line $line) {
        if (!empty($line->return)) {
            $this->block->def($line->return, $line->code);
        }
        return $this;
    }

    public function getValue($key) {
        $key = trim($key);
        if (is_numeric($key)) {
            return $key;
        }
        if (strpos($key, "'") === 0) {
            return $key;
        }
        return $this->block->get($key);
    }


    public function deADD (Line $line) {
        list($a, $b) = explode(',', $line->operands);
        $this->content = $line->code = $this->getValue($a).' + '. $this->getValue($b);
    }

    public function deADD_ARRAY_ELEMENT (Line $line) {

    }

    public function deADD_CHAR (Line $line) {
        $args = explode(',', $line->operands);
        $args[count($args) - 1] = "'".chr($args[count($args) - 1])."'";
        $this->content = $line->code = implode(' . ', $args);
    }

    public function deADD_INTERFACE (Line $line) {

    }

    public function deADD_STRING (Line $line) {
        $args = explode(',', $line->operands);
        $this->content = $line->code = implode(' . ', $args);
    }

    public function deADD_VAR (Line $line) {
        $args = explode(',', $line->operands);
        $args[count($args) - 1] = $this->getValue($args[count($args) - 1]);
		$this->content = $line->code = implode(' . ', $args);
    }

    public function deASSIGN (Line $line) {
        list($k, $v) = explode(',', $line->operands);
        $this->content = $line->code = sprintf('%s = %s', $this->getValue($k), $this->getValue($v));
    }

    public function deASSIGN_ADD (Line $line) {
		list($a, $b) = explode(',',$line->operands);
        $this->content = $line->code = $this->getValue($a).' += '.$this->getValue($b);
    }

    public function deASSIGN_BW_AND (Line $line) {
		list($a, $b) = explode(',',$line->operands);
		$this->content = $line->code = $this->getValue($a).' &= '.$this->getValue($b);
    }

    public function deASSIGN_BW_OR (Line $line) {
        list($e, $f) = explode(',',$line->operands);
        $this->content = $line->code = $this->getValue($e).' |= '.$this->getValue($f);
    }

    public function deASSIGN_BW_XOR (Line $line) {
		 list($k, $v) = explode(',', $line->operands);
        $this->content = $line->code = $this->getValue($k).' ^= '. $this->getValue($v);
    }

    public function deASSIGN_CONCAT (Line $line) {
        list($k, $v) = explode(',', $line->operands);
        $this->content = $line->code = $this->getValue($k).' .= '. $this->getValue($v);
    }

    public function deASSIGN_DIM (Line $line) {

    }

    public function deASSIGN_DIV (Line $line) {
        list($k, $v) = explode(',', $line->operands);
        $this->content = $line->code = $this->getValue($k).' /= '. $this->getValue($v);
    }

    public function deASSIGN_MOD (Line $line) {
        list($k, $v) = explode(',', $line->operands);
        $this->content = $line->code = $this->getValue($k).' %= '. $this->getValue($v);
    }

    public function deASSIGN_MUL (Line $line) {
        list($k, $v) = explode(',', $line->operands);
        $this->content = $line->code = $this->getValue($k).' *= '. $this->getValue($v);
    }

    public function deASSIGN_OBJ (Line $line) {
        list($k, $v) = explode(',', $line->operands);
        $line->code = $this->getValue($k).'->'. trim($v, '\'');
        $this->content =  $line->code.' = '.$this->deLine($this->next());
        $this->i ++;
    }

    public function deASSIGN_REF (Line $line) {

    }

    public function deASSIGN_SL (Line $line) {
        list($k, $v) = explode(',', $line->operands);
        $this->content = $line->code = $this->getValue($k).' <<= '. $this->getValue($v);
    }

    public function deASSIGN_SR (Line $line) {
        list($k, $v) = explode(',', $line->operands);
        $this->content = $line->code = $this->getValue($k).' >>= '. $this->getValue($v);
    }

    public function deASSIGN_SUB (Line $line) {
        list($k, $v) = explode(',', $line->operands);
        $this->content = $line->code = $this->getValue($k).' -= '. $this->getValue($v);
    }

    public function deBEGIN_SILENCE (Line $line) {

    }

    public function deBOOL (Line $line) {
        $this->content = $line->code = $this->getValue($line->operands);
    }

    public function deBOOL_NOT (Line $line) {
        $this->content = $line->code = ' !'.$this->getValue($line->operands);
    }

    public function deBOOL_XOR (Line $line) {
        list($k, $v) = explode(',', $line->operands);
        $this->content = $line->code = $this->getValue($k).' xor '. $this->getValue($v);
    }

    public function deBRK (Line $line) {
        $this->content = $line->code = 'break';
    }

    public function deBW_AND (Line $line) {
        list($k, $v) = explode(',', $line->operands);
        $this->content = $line->code = $this->getValue($k).'&'.$this->getValue($v);
    }

    public function deBW_NOT (Line $line) {
        $this->content = $line->code = '~'.$this->getValue($line->operands);
    }

    public function deBW_OR (Line $line) {
        list($k, $v) = explode(',', $line->operands);
        $this->content = $line->code = $this->getValue($k).'|'.$this->getValue($v);
    }

    public function deBW_XOR (Line $line) {
        list($k, $v) = explode(',', $line->operands);
        $this->content = $line->code = $this->getValue($k).' ^ '. $this->getValue($v);
    }

    public function deCASE (Line $line) {

    }

    public function deCAST (Line $line) {
        $this->content = $line->code = '(int)'.$this->getValue($line->operands);
    }

    public function deCATCH (Line $line) {

    }

    public function deCLONE (Line $line) {
        $this->content = $line->code = 'clone '.$this->getValue($line->operands);
    }

    public function deCONCAT (Line $line) {
        list($k, $v) = explode(',', $line->operands);
        $this->content = $line->code = $this->getValue($k).' . '. $this->getValue($v);
    }

    public function deCONT (Line $line) {

    }

    public function deDECLARE_CLASS (Line $line) {
        $this->content = $line->code = "class ". $this->getValue($line->operands);
    }

    public function deDECLARE_CONST (Line $line) {

    }

    public function deDECLARE_FUNCTION (Line $line) {

    }

    public function deDECLARE_INHERITED_CLASS (Line $line) {

    }

    public function deDECLARE_INHERITED_CLASS_DELAYED (Line $line) {

    }

    public function deDIV (Line $line) {
        list($k, $v) = explode(',', $line->operands);
        $this->content = $line->code = $this->getValue($k).'/'.$this->getValue($v);
    }

    public function deDO_FCALL (Line $line) {
        $this->content = $line->code = $this->getValue($line->operands)."()";
		
	}
		
    public function deDO_FCALL_BY_NAME (Line $line) {

    }

    public function deECHO (Line $line) {
        $this->content = $line->code = "echo ".$this->getValue($line->operands);
    }

    public function deEND_SILENCE (Line $line) {

    }

    public function deEXIT (Line $line) {
        $this->content = $line->code = sprintf('die(%s)', $this->getValue($line->operands));
    }

    public function deEXT_FCALL_BEGIN (Line $line) {

    }

    public function deEXT_FCALL_END (Line $line) {

    }

    public function deEXT_NOP (Line $line) {

    }

    public function deEXT_STMT (Line $line) {

    }

    public function deFE_FETCH (Line $line) {

    }

    public function deFE_RESET (Line $line) {

    }

    public function deFETCH_CLASS (Line $line) {

    }

    public function deFETCH_CONSTANT (Line $line) {

    }

    public function deFETCH_DIM_FUNC_ARG (Line $line) {

    }

    public function deFETCH_DIM_IS (Line $line) {

    }

    public function deFETCH_DIM_R (Line $line) {

    }

    public function deFETCH_DIM_RW (Line $line) {

    }

    public function deFETCH_DIM_TMP_VAR (Line $line) {

    }

    public function deFETCH_DIM_UNSET (Line $line) {

    }

    public function deFETCH_DIM_W (Line $line) {

    }

    public function deFETCH_FUNC_ARG (Line $line) {

    }

    public function deFETCH_IS (Line $line) {

    }

    public function deFETCH_OBJ_FUNC_ARG (Line $line) {

    }

    public function deFETCH_OBJ_IS (Line $line) {

    }

    public function deFETCH_OBJ_R (Line $line) {

    }

    public function deFETCH_OBJ_RW (Line $line) {

    }

    public function deFETCH_OBJ_UNSET (Line $line) {

    }

    public function deFETCH_OBJ_W (Line $line) {

    }

    public function deFETCH_R (Line $line) {

    }

    public function deFETCH_RW (Line $line) {

    }

    public function deFETCH_UNSET (Line $line) {

    }

    public function deFETCH_W (Line $line) {

    }

    public function deFREE (Line $line) {
                  $this->content = $line->code = $this->getValue($line->operands);
    }

    public function deGOTO (Line $line) {

    }

    public function deHANDLE_EXCEPTION (Line $line) {

    }

    public function deINCLUDE_OR_EVAL (Line $line) {
		
		
		list($k, $v) = explode(',', $line->operands);
       
		
           $this->content = $line->code = "$this->getValue($v)"."$this->getValue($k)";
    }

    /**
     * @param Line $line
     * @param $i
     */
    public function deINIT_ARRAY (Line $line, $i) {
        if (empty($line->operands)) {
            $line->code = '[]';
            return;
        }
        $args = [];
        foreach ($this->lines as $k => $l) {
            if ($l->op != 'INIT_ARRAY'
                && $l->op != 'ADD_ARRAY_ELEMENT') {
                continue;
            }
            if ($i > $k) {
                continue;
            }
            if ($l->return != $line->return) {
                if ($l->op != 'INIT_ARRAY') {
                    continue;
                }
                $this->deINIT_ARRAY($l, $k);
                $args[] = $l->operands;
                continue;
            }
            $args[] = $this->getValue($l->operands);
        }
        $this->content = $line->code = '['.implode(', ', $args).']';
    }

    public function deINIT_FCALL_BY_NAME (Line $line) {

    }

    public function deINIT_METHOD_CALL (Line $line) {

    }

    public function deINIT_NS_FCALL_BY_NAME (Line $line) {

    }

    public function deINIT_STATIC_METHOD_CALL (Line $line) {

    }

    public function deINIT_STRING (Line $line) {

    }

    public function deINSTANCEOF (Line $line) {

    }

    public function deIS_EQUAL (Line $line) {
        list($k, $v) = explode(',', $line->operands);
       $this->content = $line->code = sprintf('(%s == %s)', $this->getValue($k), $this->getValue($v));
    }

    public function deIS_IDENTICAL (Line $line) {

    }

    public function deIS_NOT_EQUAL (Line $line) {
         list($k, $v) = explode(',', $line->operands);
      $this->content = $line->code = sprintf('(%s != %s)', $this->getValue($k), $this->getValue($v));
    }

    public function deIS_NOT_IDENTICAL (Line $line) {
         list($k, $v) = explode(',', $line->operands);
      $this->content = $line->code = sprintf('(%s !== %s)', $this->getValue($k), $this->getValue($v));
    }

    public function deIS_SMALLER (Line $line) {
     list($k, $v) = explode(',', $line->operands);
      $this->content = $line->code = sprintf('(%s < %s)', $this->getValue($k), $this->getValue($v));
    }

    public function deIS_SMALLER_OR_EQUAL (Line $line) {
        list($k, $v) = explode(',', $line->operands);
      $this->content = $line->code = sprintf('(%s <= %s)', $this->getValue($k), $this->getValue($v));
    }

    public function deISSET_ISEMPTY_DIM_OBJ (Line $line) {

    }

    public function deISSET_ISEMPTY_PROP_OBJ (Line $line) {

    }

    public function deISSET_ISEMPTY_VAR (Line $line) {

    }

    public function deJMP (Line $line) {

    }

    public function deJMPNZ (Line $line) {

    }

    public function deJMPNZ_EX (Line $line) {

    }

    public function deJMPZ (Line $line) {

    }

    public function deJMPZ_EX (Line $line) {

    }

    public function deJMPZNZ (Line $line) {

    }

    public function deMOD (Line $line) {
         list($k, $v) = explode(',', $line->operands);
      $this->content = $line->code =  $this->getValue($k).' * '. $this->getValue($v);
    }

    public function deMUL (Line $line) {
     list($k, $v) = explode(',', $line->operands);
      $this->content = $line->code =  $this->getValue($k).'*'. $this->getValue($v);
    }

    public function deNEW (Line $line) {

    }

    public function deNOP (Line $line) {
       
	
    }

    public function dePOST_DEC (Line $line) {
         $line->code = $this->getValue($line->operands).'--';
    }

    public function dePOST_DEC_OBJ (Line $line) {

    }

    public function dePOST_INC (Line $line) {
        $line->code = $this->getValue($line->operands).'++';
    }

    public function dePOST_INC_OBJ (Line $line) {

    }

    public function dePRE_DEC (Line $line) {
    $this->content = $line->code = "--".$this->getValue($line->operands);
    }
    

    public function dePRE_DEC_OBJ (Line $line) {

    }

    public function dePRE_INC (Line $line) {
         $this->content = $line->code = "++".$this->getValue($line->operands);
    }

    public function dePRE_INC_OBJ (Line $line) {

    }

    public function dePRINT (Line $line) {
        $this->content = $line->code = 'print '.$this->getValue($line->operands);
    }

    public function deQM_ASSIGN (Line $line) {

    }

    public function deRAISE_ABSTRACT_ERROR (Line $line) {

    }

    public function deRECV (Line $line) {

    }

    public function deRECV_INIT (Line $line) {

    }

    public function deRETURN (Line $line) {
		if ($this->content) {
			return;
		}
        $this->content = $line->code = sprintf('return %s', $this->getValue($line->operands));
    }

    public function deRETURN_BY_REF (Line $line) {

    }

    public function deSEND_REF (Line $line) {
        $this->deSEND_VAR($line);
    }

    public function deSEND_VAL (Line $line) {
        $this->deSEND_VAR($line);
    }

    public function deSEND_VAR (Line $line) {
        $args = [$this->deSendFunc($line)];
        while ($line = $this->next()) {
            $arg = $this->deSendFunc($line);
            $this->i ++;
            if ($line->op == 'DO_FCALL') {
                break;
            }
            $args[] = $arg;
        }
        $this->content = $line->code = sprintf('%s(%s)',
            trim($line->operands, '\''),
            implode(', ', $args));
    }

    public function deSEND_VAR_NO_REF (Line $line) {

    }

    protected function deSendFunc(Line $line) {
        if ($line->op == 'SEND_VAL') {
            return $line->operands;
        }
        if (in_array($line->op, ['SEND_REF', 'SEND_VAR', 'SEND_VAR_NO_REF'])) {
            return $this->getValue($line->operands);
        }
    }

    public function deSL (Line $line) {
        list($a, $b) = explode(',', $line->operands);
        $this->content = $line->code = sprintf('%s << %s', $this->getValue($a), $this->getValue($b));
    }

    public function deSR (Line $line) {
        list($a, $b) = explode(',', $line->operands);
        $this->content = $line->code = sprintf('%s >> %s', $this->getValue($a), $this->getValue($b));
    }

    public function deSUB (Line $line) {
        list($a, $b) = explode(',', $line->operands);
        $this->content = $line->code = sprintf('%s - %s', $this->getValue($a), $this->getValue($b));
    }

    public function deSWITCH_FREE (Line $line) {

    }

    public function deTHROW (Line $line) {

    }

    public function deTICKS (Line $line) {

    }

    public function deUNSET_DIM (Line $line) {
        $this->deUNSET_VAR($line);
    }

    public function deUNSET_OBJ (Line $line) {
        $this->deUNSET_VAR($line);
    }

    public function deUNSET_VAR (Line $line) {
        $args = [$this->deUnset($line)];
        while ($line = $this->next()) {
            $arg = $this->deUnset($line);
            if (empty($arg)) {
                break;
            }
            $args[] = $arg;
            $this->i ++;
        }
        $this->content = $line->code = sprintf('unset(%s)', implode(', ', $args));
    }

    protected function deUnset(Line $line) {
        if ($line->op == 'UNSET_VAR') {
            return $this->getValue($line->operands);
        }
        if ($line->op == 'UNSET_OBJ') {
            list($a, $b) = explode(',', $line->operands);
            return $this->getValue($a).'->'. trim($b, '\'');
        }
        if ($line->op == 'UNSET_DIM') {
            list($a, $b) = explode(',', $line->operands);
            return sprintf('%s[%s]', $this->getValue($a), $b);
        }
    }

    public function deUSER_OPCODE (Line $line) {

    }

    public function deVERIFY_ABSTRACT_CLASS (Line $line) {

    }

    public function deZEND_DECLARE_LAMBDA_FUNCTION (Line $line) {

    }

    public function deZEND_JMP_SET (Line $line) {

    }

}