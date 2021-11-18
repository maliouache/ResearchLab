clear data model options parama
    
	a=load('data1.txt');
	e=size(a);
	n0=e(1);
    for i=1:1:n0
        if (a(i,2)>=0.03)&&(a(i+1,2)<0.03)
            n=i;
        end
    end
    b=[];

        for j=1:2:n
            b=[b;a(j,:)];
        end
        for j=n+1:2:n0
            b=[b;a(j,:)];
        end

    data.xdata = b(:,1);
    data.ydata = b(:,2);
figure(1); clf
plot(data.xdata,data.ydata,'s');
xlim([0 20]); ylim([0 0.4]); xlabel('time s'); ylabel('concentration mol/L');hold on;
%%time
tstep=10e-04;
tstep2(1)=tstep;
T=10;
i=1;
t(i)=0;
while t(i)<=T
    for j=1:1:750
        i=i+1;
        t(i)=t(i-1)+tstep;
        tstep2(i-1)=tstep;
    end
    if tstep<( 0.019   )
    tstep=tstep*1.15;
    end
end
m=1:1:51;
modelfun = @(x,theta) interp1(t,( adepg_stept(theta(1),theta(2),theta(3),theta(4),theta(5),theta(6)) ),x,'linear') ;

ssfun    = @(theta,data) (sum(  (data.ydata-modelfun(data.xdata,theta)).^2.*m'  ).^0.5 /n0)         ;
%[tmin,ssmin]=fminsearch(ssfun,[27;0.8;800;15;0.113;0.75],[],data)
n = length(data.xdata);
p = 2;
%mse = ssmin/(n-p) % estimate for the error variance

params = {
    {'theta1', 52.9395,  0, Inf,  10.58, Inf,  1,  0}
    {'theta2', 4.105466 0, Inf,  5.1, Inf,  1,  0}
    {'theta3', 597.06134, 0, Inf,  60.80, Inf,  1,  0}
    {'theta4', 28.09799, 0, Inf,  0.8, Inf,  1,  0}
    {'theta5', 0.28689, 0, Inf,  0.021, Inf,  1,  0}
    {'theta6', 0.75495, 0, 1,  0.125, Inf,  1,  0}

    };


model.ssfun  = ssfun;
model.sigma2 = 0.01^2;
options.nsimu = 800;
options.updatesigma = 1;
options.qcov = eye(11)*0.075;


[res,chain,s2chain] = mcmcrun(model,data,params,options);

% for i=1:1:6
% theta(i)=chain(options.nsimu,i);
% end
% 
% y=adepg_stept(theta(1),theta(2),theta(3),theta(4),theta(5),theta(6));
%  hold on; plot(t,y,'r');hold on; 

figure(2); clf
mcmcplot(chain,[],res,'chainpanel');

 er=0;
for i=1:1:50
    a(i,3)=interp1(t,y,a(i,1),'linear');
    er=er+(a(i,3)-a(i,2))^2;
end