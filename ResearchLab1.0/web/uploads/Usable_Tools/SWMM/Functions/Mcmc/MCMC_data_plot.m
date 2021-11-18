      clear
      clc
      close all
      addpath('mcmcstat_step31');
      addpath('functions');
      namecases={'Arti_1','Field','Gold_1','Gold_3','Gold_2','jura_AUS02','Lauber3','Massei1','Massei4'};
      
      num=1;
      root=pwd;

for num=2:2
    b=load([ 'TF' namecases{num} 'chain_noinilog.txt']);
    
      n1=size(b,1);
n2=size(b,2);
n0=5000;
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
 mcmcplot(b(n1-n0:n1,1:n2-1),[],results,'pairs');
set(findall(gcf,'-property','FontSize'),'fontweight','normal','FontSize',12)

set(gcf,'unit','normalized','position',[0.0,0.0,0.8,1]);

% for j=1:n2
%     para(num,j)=mean(b(15000:16000,j))
% end
for j=1:n2-1
    para(num,j)=mean(b2(1:n0,j))
end


for j=1:n2-1
    para2(num,j)=mean(b2(n3+1:n3+1+20,j))
end
% for j=1:n2-1
%     paramin(num,j)=min(b(n1-n0:n1,j))
% end
% for j=1:n2-1
%     paramaz(num,j)=max(b(n1-n0:n1,j))
% end
    end

% close all
