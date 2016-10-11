<?php
namespace Netsensia\Uci\Tournament;

use Netsensia\Uci\Engine;
use Ryanhs\Chess\Chess;
use Netsensia\Uci\Tournament;
use Netsensia\Uci\Match;
use Netsensia\Tournament\RoundRobin\Schedule;
use Zelenin\Elo\Player;

class RoundRobin extends Tournament
{ 
    
    const ORDER_ELO = 1;
    const ORDER_SCORE = 2;
    const ORDER_NAME = 3;
    
    /**
     * {@inheritDoc}
     * @see \Netsensia\Uci\Tournament::showTable()
     */ 
    public function showTable($order = self::ORDER_SCORE)
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
            if ($a['engine']->getElo() == $b['engine']->getElo()) {
                return 0;
            }
            
            return $a['engine']->getElo() > $b['engine']->getElo() ? -1 : 1;
        });

        echo str_pad('Engine', 20);
        echo str_pad('ELO', 6);
        echo str_pad('W', 5);
        echo str_pad('L', 5);
        echo str_pad('D', 5);
        echo str_pad('Score', 10);
        echo PHP_EOL;
        echo str_pad('', 47, '-');
        echo PHP_EOL;
        
        foreach ($engineList as $e) {
            
            echo str_pad($e['engine']->getName(), 20);
            echo str_pad(floor($e['engine']->getElo()), 6);
            
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
        
        usort($this->engines, function($a, $b) {
            return rand(-1,1);    
        });

        for ($i=0; $i<2; $i++) {
            $schedule->reset();
            $pairing = $schedule->getNextPairing();
            while ($pairing !== null) {
                
                // looks complex, but just assigns white and black depending on which iteration we are on
                $whiteIndex = $pairing[$i % 2] - 1;
                $blackIndex = $pairing[abs(($i % 2) - 1)] - 1;
                
                $whiteEngine = $this->engines[$whiteIndex];
                $blackEngine = $this->engines[$blackIndex];
                
                if ($this->output) {
                    echo $whiteEngine['engine']->getName() . ' v ' . $blackEngine['engine']->getName() . PHP_EOL;
                }
                
                $match = new Match($whiteEngine['engine'], $blackEngine['engine']);
                $result = $match->play();
                
                if ($this->output) {
                    echo $result['reason'] . PHP_EOL;
                }
                
                $eloWhite = new Player($whiteEngine['engine']->getElo());
                $eloBlack = new Player($blackEngine['engine']->getElo());
                $eloMatch = new \Zelenin\Elo\Match($eloWhite, $eloBlack);
                
                if ($result['result'] == Match::DRAW) {
                    $eloMatch->setScore(0.5, 0.5)->setK(32)->count();
                } else {
                    $eloMatch->setScore($result['result'] == Match::WHITE_WIN ? 1 : 0, $result['result'] == Match::BLACK_WIN ? 1 : 0)->setK(32)->count();
                }
                
                if (strpos($whiteEngine['engine']->getName(), 'No Adjust') === false) {
                    $whiteEngine['engine']->setElo($eloMatch->getPlayer1()->getRating());
                }
                if (strpos($blackEngine['engine']->getName(), 'No Adjust') === false) {
                    $blackEngine['engine']->setElo($eloMatch->getPlayer2()->getRating());
                }
                
                $this->engines[$whiteIndex]['matches'][] = $match;
                $this->engines[$blackIndex]['matches'][] = $match;
                
                $this->showTable();
                
                $pairing = $schedule->getNextPairing();
            }
        }
        
    }
}
