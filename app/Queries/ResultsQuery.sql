select g.homeTeam,
       g.awayTeam,
       g.commenceTime,
       gbl.homeTeamSpread as sharpHomeSpread,
       gbl2.homeTeamSpread as nonSharpSpread,
       c2.title,
       c.title,
       simulated_bets.*

from simulated_bets
         join game_betting_lines gbl on gbl.id = simulated_bets.sharpBettingLineId
         join game_betting_lines gbl2 on gbl2.id = simulated_bets.nonSharpBettingLineId
         join games g on gbl.gameId = g.id
         join casinos c on gbl2.casinoId = c.id
         join casinos c2 on gbl.casinoId = c2.id

where gbl2.casinoId in (1,3,7,13)
order by  commenceTime;
