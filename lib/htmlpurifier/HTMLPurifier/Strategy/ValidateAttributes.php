<?php



class HTMLPurifier_Strategy_ValidateAttributes extends HTMLPurifier_Strategy
{

    
    public function execute($tokens, $config, $context)
    {
                $validator = new HTMLPurifier_AttrValidator();

        $token = false;
        $context->register('CurrentToken', $token);

        foreach ($tokens as $key => $token) {

                                    if (!$token instanceof HTMLPurifier_Token_Start && !$token instanceof HTMLPurifier_Token_Empty) {
                continue;
            }

                        if (!empty($token->armor['ValidateAttributes'])) {
                continue;
            }

                        $validator->validateToken($token, $config, $context);
        }
        $context->destroy('CurrentToken');
        return $tokens;
    }
}

