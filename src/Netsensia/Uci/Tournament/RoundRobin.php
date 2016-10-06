<?php
namespace Netsensia\Uci\Tournament;

use Netsensia\Uci\Engine;
use Ryanhs\Chess\Chess;
use Netsensia\Uci\Tournament;
use Netsensia\Uci\Match;

class RoundRobin extends Tournament
{ 
    /**
     * {@inheritDoc}
     * @see \Netsensia\Uci\Tournament::showTable()
     */ 
    public function showTable()
    {
        $engineList = $this->engines;
        
        for ($i=0; $i<count($engineList); $i++) {
            
            $win = 0;
            $loss = 0;
            $draw = 0;
            foreach ($engineList[$i]['matches'] as $match) {
                
                $name = $engineList[$i]['engine']->getName();
                if ($match->getWhite()->getName() == $name) {
                    $engineSide = Chess::WHITE;
                }
                elseif ($match->getBlack()->getName() == $name) {
                    $engineSide = Chess::BLACK;
                } else {
                    throw new \Exception('Could not determine colour of engine player');
                }
                
                switch ($match->getResult()) {
                    case Match::WHITE_WIN :
                        if ($engineSide == Chess::WHITE) {
                            $win ++;
                        } else {
                            $loss ++;
                        }
                        break;
                    case Match::BLACK_WIN : 
                        if ($engineSide == Chess::BLACK) {
                            $win ++;
                        } else {
                            $loss ++;
                        }
                        break;
                    case Match::DRAW : 
                        $draw ++;
                        break;
                }
                
                $engineList[$i]['results'] = [
                    'win' => $win,
                    'loss' => $loss,
                    'draw' => $draw,
                ];
                
                $engineList[$i]['score'] = $win + ($draw / 2);

            }
        }

        usort($engineList, function($a, $b) {
            if ($a['score'] == $b['score']) {
                return 0;
            }
            
            return $a['score'] > $b['score'] ? -1 : 1;
        });

        echo str_pad('Engine', 20);
        echo str_pad('W', 5);
        echo str_pad('L', 5);
        echo str_pad('D', 5);
        echo str_pad('Score', 10);
        echo PHP_EOL;
        echo str_pad('', 40, '-');
        echo PHP_EOL;
        
        foreach ($engineList as $e) {
            
            echo str_pad($e['engine']->getName(), 20);
            
            echo str_pad($e['results']['win'], 5);
            echo str_pad($e['results']['loss'], 5);
            echo str_pad($e['results']['draw'], 5);
            echo str_pad($e['score'], 10);
            
            echo PHP_EOL;
        }
        
        echo str_pad('', 40, '-');
        echo PHP_EOL . PHP_EOL;
    }
    
    /**
     * {@inheritDoc}
     * @see \Netsensia\Uci\Tournament::start()
     */
    public function start()
    {
        $engineCount = count($this->engines);
        
        for ($whiteEngineIndex=0; $whiteEngineIndex<$engineCount; $whiteEngineIndex++) {
            for ($blackEngineIndex=0; $blackEngineIndex<$engineCount; $blackEngineIndex++) {
                
                if ($whiteEngineIndex != $blackEngineIndex) {
                    
                    $whiteEngine = $this->engines[$whiteEngineIndex];
                    $blackEngine = $this->engines[$blackEngineIndex];
                    
                    $match = new Match($whiteEngine['engine'], $blackEngine['engine']);
                    $match->play();
                    
                    $this->engines[$whiteEngineIndex]['matches'][] = $match;
                    $this->engines[$blackEngineIndex]['matches'][] = $match;
                    
                    $this->showTable();
                }
            }
        }
    }
}
