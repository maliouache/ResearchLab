      clear
      clc
      close all
      addpath('mcmcstat_step31');
      addpath('functions');
      namecases={'Arti_1','Field','Gold_1','Gold_3','Gold_2','jura_AUS02','Lauber3','Massei1','Massei4'};
      
      num=1;
      root=pwd;

for num=3:3
    b1=load([ 'TF' namecases{num} 'chain_noini.txt']);
    b2=load([ 'TF' namecases{num} 'chain_noinilog.txt']);

      n1=size(b1,1);
n2=size(b1,2);
n0=15000;
    b=[b1(n1-n0:n1,:);b2(n1-n0:n1,:)];

    [a,position]=sort(b(:,n2));
        for i=1:1:n1
    b2(i,:)=b(position(i),:);
        end
        n3=1;
    for j=1:n1
    if b2(j,8)<0
        n3=n3+1;
    end
end
     
    figure(num); clf
results={'A1','A2','N1','N2','tau1','tau2','w1'};
%  mcmcplot(b2(n3+1:n3+1+n0,1:n2-1),[],results,'pairs');
 mcmcplot(b(:,1:n2-1),[],results,'pairs');
set(findall(gcf,'-property','FontSize'),'fontweight','normal','FontSize',12)

set(gcf,'unit','normalized','position',[0.0,0.0,0.8,1]);

meanv=(max(b)-min(b))*0.5+min(b);
range=(max(b)-min(b));
para2(num,:)=meanv;
    end

% close all
