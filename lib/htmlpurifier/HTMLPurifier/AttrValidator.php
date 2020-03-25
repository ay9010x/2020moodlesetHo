<?php


class HTMLPurifier_AttrValidator
{

    
    public function validateToken($token, $config, $context)
    {
        $definition = $config->getHTMLDefinition();
        $e =& $context->get('ErrorCollector', true);

                $ok =& $context->get('IDAccumulator', true);
        if (!$ok) {
            $id_accumulator = HTMLPurifier_IDAccumulator::build($config, $context);
            $context->register('IDAccumulator', $id_accumulator);
        }

                $current_token =& $context->get('CurrentToken', true);
        if (!$current_token) {
            $context->register('CurrentToken', $token);
        }

        if (!$token instanceof HTMLPurifier_Token_Start &&
            !$token instanceof HTMLPurifier_Token_Empty
        ) {
            return;
        }

                        $d_defs = $definition->info_global_attr;

                $attr = $token->attr;

                        foreach ($definition->info_attr_transform_pre as $transform) {
            $attr = $transform->transform($o = $attr, $config, $context);
            if ($e) {
                if ($attr != $o) {
                    $e->send(E_NOTICE, 'AttrValidator: Attributes transformed', $o, $attr);
                }
            }
        }

                        foreach ($definition->info[$token->name]->attr_transform_pre as $transform) {
            $attr = $transform->transform($o = $attr, $config, $context);
            if ($e) {
                if ($attr != $o) {
                    $e->send(E_NOTICE, 'AttrValidator: Attributes transformed', $o, $attr);
                }
            }
        }

                                $defs = $definition->info[$token->name]->attr;

        $attr_key = false;
        $context->register('CurrentAttr', $attr_key);

                        foreach ($attr as $attr_key => $value) {

                        if (isset($defs[$attr_key])) {
                                if ($defs[$attr_key] === false) {
                                                                                                                        $result = false;
                } else {
                                        $result = $defs[$attr_key]->validate(
                        $value,
                        $config,
                        $context
                    );
                }
            } elseif (isset($d_defs[$attr_key])) {
                                                $result = $d_defs[$attr_key]->validate(
                    $value,
                    $config,
                    $context
                );
            } else {
                                $result = false;
            }

                        if ($result === false || $result === null) {
                                                if ($e) {
                    $e->send(E_ERROR, 'AttrValidator: Attribute removed');
                }

                                unset($attr[$attr_key]);
            } elseif (is_string($result)) {
                                                
                                $attr[$attr_key] = $result;
            } else {
                            }

                                                                    }

        $context->destroy('CurrentAttr');

        
                foreach ($definition->info_attr_transform_post as $transform) {
            $attr = $transform->transform($o = $attr, $config, $context);
            if ($e) {
                if ($attr != $o) {
                    $e->send(E_NOTICE, 'AttrValidator: Attributes transformed', $o, $attr);
                }
            }
        }

                foreach ($definition->info[$token->name]->attr_transform_post as $transform) {
            $attr = $transform->transform($o = $attr, $config, $context);
            if ($e) {
                if ($attr != $o) {
                    $e->send(E_NOTICE, 'AttrValidator: Attributes transformed', $o, $attr);
                }
            }
        }

        $token->attr = $attr;

                if (!$current_token) {
            $context->destroy('CurrentToken');
        }

    }


}

