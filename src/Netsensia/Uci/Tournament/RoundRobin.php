<?php
namespace Netsensia\Uci\Tournament;

use Netsensia\Uci\Engine;
use Ryanhs\Chess\Chess;
use Netsensia\Uci\Tournament;
use Netsensia\Uci\Match;
use Netsensia\Tournament\RoundRobin\Schedule;

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
        $schedule = new Schedule(count($this->engines));

        for ($i=0; $i<2; $i++) {
            $schedule->reset();
            $pairing = $schedule->getNextPairing();
            while ($pairing !== null) {
                
                // looks complex, but just assigns white and black depending on which iteration we are on
                $whiteIndex = $pairing[$i % 2] - 1;
                $blackIndex = $pairing[abs(($i % 2) - 1)] - 1;
                
                $whiteEngine = $this->engines[$whiteIndex];
                $blackEngine = $this->engines[$blackIndex];
                
                echo $whiteEngine['engine']->getName() . ' v ' . $blackEngine['engine']->getName() . PHP_EOL;
                
                $match = new Match($whiteEngine['engine'], $blackEngine['engine']);
                $match->play();
                
                $this->engines[$whiteIndex]['matches'][] = $match;
                $this->engines[$blackIndex]['matches'][] = $match;
                
                $this->showTable();
                
                $pairing = $schedule->getNextPairing();
            }
        }
        
    }
}
