select g.id, g.homeTeam, g.awayTeam, c.title, simulated_bets.won, datediff(g.commenceTime, simulated_bets.created_at) as dif, count(simulated_bets.id) as mycount
#select count(*)
from simulated_bets
join game_betting_lines gbl on gbl.id = simulated_bets.sharpBettingLineId
join game_betting_lines gbl2 on gbl2.id = simulated_bets.nonSharpBettingLineId
join games g on gbl.gameId = g.id
join casinos c on gbl2.casinoId = c.id
join casinos c2 on gbl.casinoId = c2.id
join scores s on s.id = simulated_bets.scoreId
    #and simulated_bets.won =0
where gbl2.casinoId in (16)
and gbl.casinoId in (4)
and gbl2.isCurrent
#and datediff(g.commenceTime, simulated_bets.created_at) < 10
and datediff(g.commenceTime, simulated_bets.created_at) > 1
#and g.id > 150
#and abs(gbl.homeTeamSpread - gbl2.homeTeamSpread) > 2.0

#and mycount > 5
group by g.id,c.id,simulated_bets.won;

#
#group by g.id,simulated_bets.won;
#order by  commenceTime;

select g.id, g.created_at, g.commenceTime, simulated_bets.won, simulated_bets.created_at, c2.title, gbl.homeTeamSpread, c.title, gbl2.homeTeamSpread, abs(gbl.homeTeamSpread - gbl2.homeTeamSpread) as dif
#select count(*)
from simulated_bets
         join game_betting_lines gbl on gbl.id = simulated_bets.sharpBettingLineId
         join game_betting_lines gbl2 on gbl2.id = simulated_bets.nonSharpBettingLineId
         join games g on gbl.gameId = g.id
         join casinos c on gbl2.casinoId = c.id
         join casinos c2 on gbl.casinoId = c2.id
         join scores s on s.id = simulated_bets.scoreId
#and abs(gbl.homeTeamSpread - gbl2.homeTeamSpread) > 1.5
     #and simulated_bets.won =0
where gbl2.casinoId in (7)
  and gbl.casinoId in (4)
  #and g.id >120
  #and datediff(g.commenceTime, simulated_bets.created_at)  > 5
#and mycount > 5

order by g.id, simulated_bets.created_at;

select count(*) from simulated_bets
         where won is not null;


select c.title, count(gbl.casinoId) as totalSim, sum(sb.won) as won
from simulated_bets sb
join game_betting_lines gbl on gbl.id = sb.nonSharpBettingLineId
join game_betting_lines g on g.id = sb.sharpBettingLineId
join casinos c on gbl.casinoId = c.id
where g.casinoId =4
and g.id >150
group by gbl.casinoId ;
