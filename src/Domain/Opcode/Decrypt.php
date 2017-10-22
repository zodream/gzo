<?php
namespace Zodream\Module\Gzo\Domain\Opcode;

use Zodream\Disk\File;
use Zodream\Infrastructure\Base\MagicObject;

class Decrypt extends MagicObject {

    public function setValue(Line $line) {
        if (!empty($line->return)) {
            $this->setAttribute($line->return, $line->code);
        }
        return $this;
    }

    public function getValue($key) {
        $key = trim($key);
        if (is_numeric($key)) {
            return $key;
        }
        if ($key[0] == "'") {
            return $key;
        }
        return $this->getAttribute($key);
    }

    public function deADD (Line $line) {
        list($a, $b) = explode(',', $line->operands);
        $line->code = $this->getValue($a).' + '. $this->getValue($b);
    }

    public function deADD_ARRAY_ELEMENT (Line $line) {

    }

    public function deADD_CHAR (Line $line) {

    }

    public function deADD_INTERFACE (Line $line) {

    }

    public function deADD_STRING (Line $line) {

    }

    public function deADD_VAR (Line $line) {

    }

    public function deASSIGN (Line $line) {
        list($k, $v) = explode(',', $line->operands);
        $line->code = sprintf('%s = %s', $this->getValue($k), $this->getValue($v));
    }

    public function deASSIGN_ADD (Line $line) {

    }

    public function deASSIGN_BW_AND (Line $line) {

    }

    public function deASSIGN_BW_OR (Line $line) {

    }

    public function deASSIGN_BW_XOR (Line $line) {

    }

    public function deASSIGN_CONCAT (Line $line) {

    }

    public function deASSIGN_DIM (Line $line) {

    }

    public function deASSIGN_DIV (Line $line) {

    }

    public function deASSIGN_MOD (Line $line) {

    }

    public function deASSIGN_MUL (Line $line) {

    }

    public function deASSIGN_OBJ (Line $line) {

    }

    public function deASSIGN_REF (Line $line) {

    }

    public function deASSIGN_SL (Line $line) {

    }

    public function deASSIGN_SR (Line $line) {

    }

    public function deASSIGN_SUB (Line $line) {

    }

    public function deBEGIN_SILENCE (Line $line) {

    }

    public function deBOOL (Line $line) {

    }

    public function deBOOL_NOT (Line $line) {

    }

    public function deBOOL_XOR (Line $line) {

    }

    public function deBRK (Line $line) {

    }

    public function deBW_AND (Line $line) {

    }

    public function deBW_NOT (Line $line) {

    }

    public function deBW_OR (Line $line) {

    }

    public function deBW_XOR (Line $line) {

    }

    public function deCASE (Line $line) {

    }

    public function deCAST (Line $line) {

    }

    public function deCATCH (Line $line) {

    }

    public function deCLONE (Line $line) {

    }

    public function deCONCAT (Line $line) {

    }

    public function deCONT (Line $line) {

    }

    public function deDECLARE_CLASS (Line $line) {

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

    }

    public function deDO_FCALL (Line $line) {

    }

    public function deDO_FCALL_BY_NAME (Line $line) {

    }

    public function deECHO (Line $line) {
        $line->code = 'echo '.$this->getValue($line->operands);
    }

    public function deEND_SILENCE (Line $line) {

    }

    public function deEXIT (Line $line) {

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

    }

    public function deGOTO (Line $line) {

    }

    public function deHANDLE_EXCEPTION (Line $line) {

    }

    public function deINCLUDE_OR_EVAL (Line $line) {

    }

    /**
     * @param Line $line
     * @param $i
     * @param Line[] $lines
     */
    public function deINIT_ARRAY (Line $line, $i, $lines) {
        if (empty($line->operands)) {
            $line->code = '[]';
            return;
        }
        $args = [];
        foreach ($lines as $k => $l) {
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
                $this->deINIT_ARRAY($l, $k, $lines);
                $args[] = $l->operands;
                continue;
            }
            $args[] = $this->getValue($l->operandsp);
        }
        $line->code = '['.implode(', ', $args).']';
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

    }

    public function deIS_IDENTICAL (Line $line) {

    }

    public function deIS_NOT_EQUAL (Line $line) {

    }

    public function deIS_NOT_IDENTICAL (Line $line) {

    }

    public function deIS_SMALLER (Line $line) {

    }

    public function deIS_SMALLER_OR_EQUAL (Line $line) {

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

    }

    public function deMUL (Line $line) {

    }

    public function deNEW (Line $line) {

    }

    public function deNOP (Line $line) {

    }

    public function dePOST_DEC (Line $line) {

    }

    public function dePOST_DEC_OBJ (Line $line) {

    }

    public function dePOST_INC (Line $line) {
        $line->code = $this->getValue($line->operands). '++';
    }

    public function dePOST_INC_OBJ (Line $line) {

    }

    public function dePRE_DEC (Line $line) {

    }

    public function dePRE_DEC_OBJ (Line $line) {

    }

    public function dePRE_INC (Line $line) {

    }

    public function dePRE_INC_OBJ (Line $line) {

    }

    public function dePRINT (Line $line) {

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

    }

    public function deRETURN_BY_REF (Line $line) {

    }

    public function deSEND_REF (Line $line) {

    }

    public function deSEND_VAL (Line $line) {

    }

    public function deSEND_VAR (Line $line) {

    }

    public function deSEND_VAR_NO_REF (Line $line) {

    }

    public function deSL (Line $line) {

    }

    public function deSR (Line $line) {

    }

    public function deSUB (Line $line) {

    }

    public function deSWITCH_FREE (Line $line) {

    }

    public function deTHROW (Line $line) {

    }

    public function deTICKS (Line $line) {

    }

    public function deUNSET_DIM (Line $line) {

    }

    public function deUNSET_OBJ (Line $line) {

    }

    public function deUNSET_VAR (Line $line) {

    }

    public function deUSER_OPCODE (Line $line) {

    }

    public function deVERIFY_ABSTRACT_CLASS (Line $line) {

    }

    public function deZEND_DECLARE_LAMBDA_FUNCTION (Line $line) {

    }

    public function deZEND_JMP_SET (Line $line) {

    }

    /**
     * 处理一行
     * @param Line[] $lines
     * @return array|Line[]
     */
    public function getLine(array $lines) {
        foreach ($lines as $i => $line) {
            $method = 'get'.$line->op;
            $this->{$method}($line, $i, $lines);
            $this->setValue($line);
        }
        return $lines;
    }


    public function setGlobal(array $lines) {

    }

    /**
     * 获取CMD 执行内容
     * @param File|string $file
     * @return bool|string
     */
    public static function getContent($file) {
        $descriptorspec = array(
            0 => array("pipe", "r"),    // stdin
            1 => array("pipe", "w"),    // stdout
            2 => array("pipe", "w")     // stderr
        );
        $cmd = 'D:\phpStudy\php\php-5.6.27-nts\php.exe -dvld.active=1 '.$file;  // 替换为你要执行的shell脚本
        $proc = proc_open($cmd, $descriptorspec, $pipes, null, null);
        // $proc为false，表明命令执行失败
        if ($proc == false) {
            return false;
        }
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $status = proc_close($proc);  // 释放proc
        return $stderr;
    }

    public static function decode($file) {
        $content = static::getContent($file);
        if (empty($content)) {
            return false;
        }
        $parts = explode('branch:', $content);
        array_pop($parts);
        foreach ($parts as $part) {
            $lines = explode("\n", $content);
            $instance = new static();
            $instance->setGlobal($lines);
            $lines = Line::parse($lines);
            ksort($lines);
            foreach ($lines as $line) {
                $instance->getLine($line);
            }
        }
    }
}