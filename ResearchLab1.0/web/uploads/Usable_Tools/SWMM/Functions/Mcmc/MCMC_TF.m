function [p]=MCMC_TF(TFpara,BTC,N)
clear data model options parama
addpath('Mcmc');
addpath('TF');
tstep=BTC(2,1)-BTC(1,1);
BTC(:,2)=BTC(:,2)/(sum(BTC(:,2))*tstep);
tm=sum(BTC(:,2).*BTC(:,1))/sum(BTC(:,2));
n0=size(BTC,1);
n=200;
tstep=max(BTC(:,1))/n;
T=BTC(n0,1);
t=tstep:tstep:T;
c=interp1(BTC(:,1),BTC(:,2),t,'linear');


    data.xdata = t';
    data.ydata = c';
    n0=size(data.xdata,2);
    figure(1); clf
    plot(data.xdata,data.ydata,'s');
    ylim([0 max(data.ydata)])
    hold on;
    r=ones(n0,1);
xlabel('time/s','FontSize',16,'FontWeight','bold'); ylabel('concentration/(mol/L)','FontSize',16,'FontWeight','bold');
set(gca,'FontSize',16);

tstep=T/200;
A1=TFpara(1);
N1=TFpara(2);
% tau1=input(3);
t=tstep:tstep:T;
s=TF(A1,N1,tm,tstep,T);
e= (1/(sum(s)*tstep))^2* sum   ( (data.ydata-interp1(t,s ,data.xdata,'linear')).^2/n0)  ^0.5   ;
modelfun = @(x,theta) interp1(t,(TF(theta(1),theta(2),tm,tstep,T )) ,x,'linear') ;
ssfun    = @(theta,data) ( sum( (     data.ydata-modelfun(data.xdata,theta)     ).^2.*r /n0)^0.5 );
params = {
    {'A1', A1,  A1*0.002, A1*500, A1, Inf,  1,  0}
    {'N1', N1,  N1*0.002, N1*500, N1, Inf,  1,  0}
    };
model.ssfun  = ssfun;
model.sigma2 = 0.01^2;
options.nsimu = 300;
options.updatesigma = 1;
[res,chain,s2chain] = mcmcrun(model,data,params,options);
 figure(1); clf
    plot(data.xdata,data.ydata,'s');hold on;
    ylim([0 max(data.ydata)])
TFpara=mean(chain(options.nsimu*0.75:options.nsimu,:));
tstep=T/200;
A1=TFpara(1);
N1=TFpara(2);
params = {
    {'A1', A1,  A1*0.002, A1*500, A1, Inf,  1,  0}
    {'N1', N1,  N1*0.002, N1*500, N1, Inf,  1,  0}
    };
model.ssfun  = ssfun;
model.sigma2 = 0.01^2;
options.nsimu = N;
options.updatesigma = 1;
[res,chain,s2chain] = mcmcrun(model,data,params,options);
p=[chain,s2chain];
end