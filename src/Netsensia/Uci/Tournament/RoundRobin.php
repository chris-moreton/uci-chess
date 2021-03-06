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
     * 
     * @return string
     */ 
    public function table($order = self::ORDER_SCORE)
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
        
        $table = 'Engine,ELO,W,L,D,Score' . PHP_EOL;
        
        foreach ($engineList as $e) {
            
            $table .= $e['engine']->getName() . ',';
            $table .= floor($e['engine']->getElo()) . ',';
            
            $table .= $e['results']['win'] . ',';
            $table .= $e['results']['loss'] . ',';
            $table .= $e['results']['draw'] . ',';
            $table .= $e['score'];
            
            $table .= PHP_EOL;
        }
        
        return $table;
    }
    
    /**
     * Play the given match
     * 
     * @param Match $match
     * @return \Netsensia\Uci\string[]
     */
    public function play(Match $match)
    {
        $result = $match->play();
        
        $whiteEngine = $match->getWhite();
        $blackEngine = $match->getBlack();
        
        $eloWhite = new Player($whiteEngine->getElo());
        $eloBlack = new Player($blackEngine->getElo());
        $eloMatch = new \Zelenin\Elo\Match($eloWhite, $eloBlack);
        
        if ($result['result'] == Match::DRAW) {
            $eloMatch->setScore(0.5, 0.5)->setK(32)->count();
        } else {
            $eloMatch->setScore($result['result'] == Match::WHITE_WIN ? 1 : 0, $result['result'] == Match::BLACK_WIN ? 1 : 0)->setK(32)->count();
        }
        
        if (strpos($whiteEngine->getName(), 'No Adjust') === false) {
            $whiteEngine->setElo($eloMatch->getPlayer1()->getRating());
        }
        if (strpos($blackEngine->getName(), 'No Adjust') === false) {
            $blackEngine->setElo($eloMatch->getPlayer2()->getRating());
        }
        
        return $result;
    }
    
    /**
     * {@inheritDoc}
     * @see \Netsensia\Uci\Tournament::start()
     */
    public function matches()
    {
        $schedule = new Schedule(count($this->engines));
        
        usort($this->engines, function($a, $b) {
            return rand(-1,1);    
        });

        for ($i=0; $i<($this->numberOfRuns * 2); $i++) {
            $schedule->reset();
            $pairing = $schedule->getNextPairing();
            while ($pairing !== null) {
                
                // looks complex, but just assigns white and black depending on which iteration we are on
                $whiteIndex = $pairing[$i % 2] - 1;
                $blackIndex = $pairing[abs(($i % 2) - 1)] - 1;
                
                $whiteEngine = $this->engines[$whiteIndex];
                $blackEngine = $this->engines[$blackIndex];
                
                $match = new Match($whiteEngine['engine'], $blackEngine['engine']);
                
                $this->engines[$whiteIndex]['matches'][] = $match;
                $this->engines[$blackIndex]['matches'][] = $match;
                
                yield $match;
                
                $pairing = $schedule->getNextPairing();
            }
        }
        
    }
}
