"use client";

import Image from "next/image";
import Link from "next/link";
import { useState, type CSSProperties } from "react";
import styles from "./home-motion.module.css";

const services = ["Photography", "Film Production", "Wedding Film", "Post Production", "VFX & Animation"];
const reelFrames = [...services, ...services, ...services];

export function HomeServices() {
  const [paused, setPaused] = useState(false);

  return (
    <section id="services" className={styles.services} aria-labelledby="services-title" data-paused={paused}>
      <h2 id="services-title" lang="en">Our Services</h2>
      <ul className={styles.accessibleList}>
        {services.map((title) => <li key={title}>{title}</li>)}
      </ul>
      <div className={styles.serviceStage} aria-hidden="true">
        <div className={styles.serviceReel}>
          {reelFrames.map((title, index) => (
            <div className={styles.serviceFrame} key={index} style={{ "--angle": `${-index * 24}deg` } as CSSProperties}>
              <div className={styles.serviceThumb}>
                <Image src="/images/home/placeholder.webp" alt="" fill sizes="(width < 810px) 66px, (width < 1200px) 86px, 128px" />
              </div>
              <span>{title}</span>
            </div>
          ))}
        </div>
      </div>
      <div className={styles.serviceAside}>
        <Link href="/services">Lihat layanan ↗</Link>
        <button type="button" className={styles.motionControl} aria-pressed={paused} onClick={() => setPaused(!paused)}>
          {paused ? "Lanjutkan animasi" : "Jeda animasi"}
        </button>
      </div>
    </section>
  );
}
